"""Train demand forecasting models using Prophet."""
import pandas as pd
import joblib
from pathlib import Path
from typing import Dict, List
import logging
from datetime import datetime
import warnings

from config import settings
from data_processor import DataProcessor

# Suppress prophet warnings
warnings.filterwarnings('ignore', category=FutureWarning)
warnings.filterwarnings('ignore', category=UserWarning)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Try to import Prophet, fallback to statsmodels if it fails
try:
    from prophet import Prophet
    PROPHET_AVAILABLE = True
    logger.info("Using Prophet for forecasting")
except Exception as e:
    logger.warning(f"Prophet not available: {e}. Falling back to statsmodels")
    PROPHET_AVAILABLE = False
    from statsmodels.tsa.holtwinters import ExponentialSmoothing


class ForecastTrainer:
    """Train and save Prophet forecasting models."""
    
    def __init__(self, data_processor: DataProcessor):
        """Initialize with data processor."""
        self.data_processor = data_processor
        self.models = {}
        self.metrics = {}
        
    def train_model(
        self, 
        df: pd.DataFrame, 
        model_key: str,
        seasonality_mode: str = 'multiplicative'
    ):
        """
        Train a forecasting model on the given dataset.
        
        Args:
            df: DataFrame with 'ds' and 'y' columns
            model_key: Unique identifier for the model
            seasonality_mode: 'additive' or 'multiplicative'
        
        Returns:
            Trained model
        """
        logger.info(f"Training model for {model_key} with {len(df)} samples")
        
        # Skip if insufficient data
        if len(df) < settings.min_training_samples:
            logger.warning(f"Insufficient data for {model_key}: {len(df)} < {settings.min_training_samples}")
            return None
        
        try:
            if PROPHET_AVAILABLE:
                # Use Prophet
                model = Prophet(
                    seasonality_mode=seasonality_mode,
                    daily_seasonality=False,
                    weekly_seasonality=True,
                    yearly_seasonality=True if len(df) > 365 else False,
                    changepoint_prior_scale=0.05,
                )
                
                # Suppress Prophet's verbose logging
                import logging as prophet_logging
                prophet_logging.getLogger('prophet').setLevel(prophet_logging.WARNING)
                prophet_logging.getLogger('cmdstanpy').setLevel(prophet_logging.WARNING)
                
                model.fit(df, algorithm='Newton')
                self.models[model_key] = model
                
                # Compute metrics
                forecast = model.predict(df)
                mae = (forecast['yhat'] - df['y']).abs().mean()
                rmse = ((forecast['yhat'] - df['y']) ** 2).mean() ** 0.5
                
            else:
                # Fallback to ExponentialSmoothing
                ts = df.set_index('ds')['y']
                model = ExponentialSmoothing(
                    ts,
                    seasonal_periods=7,  # Weekly seasonality
                    trend='add',
                    seasonal='add' if seasonality_mode == 'additive' else 'mul',
                    initialization_method="estimated"
                )
                fitted_model = model.fit(optimized=True)
                self.models[model_key] = fitted_model
                
                # Compute metrics
                predictions = fitted_model.fittedvalues
                mae = (predictions - ts).abs().mean()
                rmse = ((predictions - ts) ** 2).mean() ** 0.5
            
            self.metrics[model_key] = {
                'mae': float(mae),
                'rmse': float(rmse),
                'training_samples': len(df),
                'trained_at': datetime.now().isoformat(),
                'model_type': 'prophet' if PROPHET_AVAILABLE else 'exponential_smoothing'
            }
            
            logger.info(f"Model {model_key}: MAE={mae:.2f}, RMSE={rmse:.2f}")
            return model
            
        except Exception as e:
            logger.error(f"Error training model {model_key}: {e}")
            return None
    
    def train_all_models(self) -> Dict[str, Prophet]:
        """
        Train models for all warehouse-category combinations.
        
        Returns:
            Dictionary of trained models
        """
        logger.info("Starting model training for all warehouse-category combinations")
        
        training_data = self.data_processor.prepare_training_data()
        
        for model_key, df in training_data.items():
            self.train_model(df, model_key)
        
        logger.info(f"Training complete. Trained {len(self.models)} models")
        return self.models
    
    def save_models(self, output_dir: Path = None) -> None:
        """
        Save all trained models and metrics to disk.
        
        Args:
            output_dir: Directory to save models (defaults to settings.models_path)
        """
        if output_dir is None:
            output_dir = settings.models_path
        
        output_dir.mkdir(parents=True, exist_ok=True)
        
        # Save each model
        for model_key, model in self.models.items():
            model_file = output_dir / f"{model_key}.joblib"
            joblib.dump(model, model_file)
            logger.info(f"Saved model: {model_file}")
        
        # Save metrics
        metrics_file = output_dir / "metrics.joblib"
        joblib.dump(self.metrics, metrics_file)
        logger.info(f"Saved metrics: {metrics_file}")
        
        # Save metadata
        metadata = {
            'trained_at': datetime.now().isoformat(),
            'num_models': len(self.models),
            'model_keys': list(self.models.keys()),
            'forecast_horizon_days': settings.forecast_horizon_days
        }
        metadata_file = output_dir / "metadata.joblib"
        joblib.dump(metadata, metadata_file)
        logger.info(f"Saved metadata: {metadata_file}")
    
    def load_models(self, input_dir: Path = None) -> Dict[str, Prophet]:
        """
        Load previously trained models from disk.
        
        Args:
            input_dir: Directory containing models (defaults to settings.models_path)
        
        Returns:
            Dictionary of loaded models
        """
        if input_dir is None:
            input_dir = settings.models_path
        
        if not input_dir.exists():
            logger.warning(f"Models directory not found: {input_dir}")
            return {}
        
        self.models = {}
        
        # Load each .joblib file (except metrics and metadata)
        for model_file in input_dir.glob("*.joblib"):
            if model_file.name in ['metrics.joblib', 'metadata.joblib']:
                continue
            
            model_key = model_file.stem
            try:
                self.models[model_key] = joblib.load(model_file)
                logger.info(f"Loaded model: {model_key}")
            except Exception as e:
                logger.error(f"Error loading model {model_key}: {e}")
        
        # Load metrics if available
        metrics_file = input_dir / "metrics.joblib"
        if metrics_file.exists():
            self.metrics = joblib.load(metrics_file)
        
        logger.info(f"Loaded {len(self.models)} models")
        return self.models


def main():
    """Train models and save to disk."""
    logger.info("=== Starting Demand Forecasting Model Training ===")
    
    # Initialize data processor
    data_processor = DataProcessor(settings.data_path)
    data_processor.load_data()
    
    # Show data summary
    logger.info(f"Warehouses: {data_processor.get_available_warehouses()}")
    logger.info(f"Categories: {data_processor.get_available_categories()}")
    
    # Train models
    trainer = ForecastTrainer(data_processor)
    trainer.train_all_models()
    
    # Save models
    trainer.save_models()
    
    logger.info("=== Training Complete ===")
    logger.info(f"Models saved to: {settings.models_path}")
    logger.info(f"Total models trained: {len(trainer.models)}")
    
    # Print summary metrics
    print("\n=== Model Performance Summary ===")
    for model_key, metrics in trainer.metrics.items():
        print(f"{model_key}:")
        print(f"  MAE: {metrics['mae']:.2f}")
        print(f"  RMSE: {metrics['rmse']:.2f}")
        print(f"  Samples: {metrics['training_samples']}")


if __name__ == "__main__":
    main()
