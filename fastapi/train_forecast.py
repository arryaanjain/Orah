"""Train demand forecasting models using ARIMA/SARIMAX."""
import pandas as pd
import numpy as np
from statsmodels.tsa.arima.model import ARIMA
from statsmodels.tsa.statespace.sarimax import SARIMAX
import joblib
from pathlib import Path
from typing import Dict, Tuple
import logging
from datetime import datetime
import warnings

from config import settings
from data_processor import DataProcessor

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
warnings.filterwarnings('ignore')


class ForecastTrainer:
    """Train and save ARIMA/SARIMAX forecasting models."""
    
    def __init__(self, data_processor: DataProcessor):
        """Initialize with data processor."""
        self.data_processor = data_processor
        self.models = {}
        self.metrics = {}
        
    def auto_select_order(self, series: pd.Series) -> Tuple[int, int, int]:
        """Simple automatic order selection for ARIMA."""
        n = len(series)
        if n < 30:
            return (1, 0, 1)
        elif n < 100:
            return (2, 1, 2)
        else:
            return (3, 1, 3)
    
    def train_model(self, df: pd.DataFrame, model_key: str, use_seasonal: bool = True) -> Dict:
        """Train an ARIMA or SARIMAX model."""
        logger.info(f"Training model for {model_key} with {len(df)} samples")
        
        if len(df) < settings.min_training_samples:
            logger.warning(f"Insufficient data for {model_key}")
            return None
        
        try:
            series = df.set_index('ds')['y']
            p, d, q = self.auto_select_order(series)
            
            if use_seasonal and len(series) >= 50:
                model = SARIMAX(series, order=(p, d, q), seasonal_order=(1, 0, 1, 7),
                               enforce_stationarity=False, enforce_invertibility=False)
            else:
                model = ARIMA(series, order=(p, d, q),
                             enforce_stationarity=False, enforce_invertibility=False)
            
            fitted_model = model.fit(disp=False)
            
            model_info = {
                'fitted_model': fitted_model,
                'order': (p, d, q),
                'seasonal': use_seasonal and len(series) >= 50,
                'last_date': series.index[-1],
                'freq': 'D'
            }
            
            self.models[model_key] = model_info
            
            predictions = fitted_model.fittedvalues
            actual = series[predictions.index]
            mae = (predictions - actual).abs().mean()
            rmse = ((predictions - actual) ** 2).mean() ** 0.5
            
            self.metrics[model_key] = {
                'mae': float(mae),
                'rmse': float(rmse),
                'training_samples': len(df),
                'order': f"({p},{d},{q})",
                'seasonal': use_seasonal and len(series) >= 50,
                'trained_at': datetime.now().isoformat()
            }
            
            logger.info(f"Model {model_key}: MAE={mae:.2f}, RMSE={rmse:.2f}, Order=({p},{d},{q})")
            return model_info
            
        except Exception as e:
            logger.error(f"Error training model {model_key}: {e}")
            return None
    
    def train_all_models(self) -> Dict:
        """Train models for all warehouse-category combinations."""
        logger.info("Starting model training")
        training_data = self.data_processor.prepare_training_data()
        
        for model_key, df in training_data.items():
            self.train_model(df, model_key)
        
        logger.info(f"Training complete. Trained {len(self.models)} models")
        return self.models
    
    def save_models(self, output_dir: Path = None):
        """Save all trained models."""
        if output_dir is None:
            output_dir = settings.models_path
        
        output_dir.mkdir(parents=True, exist_ok=True)
        
        for model_key, model_info in self.models.items():
            joblib.dump(model_info, output_dir / f"{model_key}.joblib")
        
        joblib.dump(self.metrics, output_dir / "metrics.joblib")
        
        metadata = {
            'trained_at': datetime.now().isoformat(),
            'num_models': len(self.models),
            'model_keys': list(self.models.keys()),
            'forecast_horizon_days': settings.forecast_horizon_days,
            'model_type': 'ARIMA/SARIMAX'
        }
        joblib.dump(metadata, output_dir / "metadata.joblib")
        logger.info(f"Saved {len(self.models)} models to {output_dir}")


def main():
    """Train models and save to disk."""
    logger.info("=== Starting Demand Forecasting Model Training ===")
    
    data_processor = DataProcessor(settings.data_path)
    data_processor.load_data()
    
    logger.info(f"Warehouses: {data_processor.get_available_warehouses()}")
    logger.info(f"Categories: {data_processor.get_available_categories()}")
    
    trainer = ForecastTrainer(data_processor)
    trainer.train_all_models()
    trainer.save_models()
    
    logger.info("=== Training Complete ===")
    logger.info(f"Total models trained: {len(trainer.models)}")
    
    print("\n=== Model Performance Summary ===")
    for model_key, metrics in trainer.metrics.items():
        print(f"{model_key}: MAE={metrics['mae']:.2f}, RMSE={metrics['rmse']:.2f}, Order={metrics['order']}")


if __name__ == "__main__":
    main()
