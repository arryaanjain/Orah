"""Forecast service for generating predictions using ARIMA models."""
import pandas as pd
import joblib
from pathlib import Path
from typing import Dict, List, Optional
from datetime import datetime, timedelta
import logging

from config import settings

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class ForecastService:
    """Service for loading models and generating forecasts."""
    
    def __init__(self, models_dir: Path = None):
        """Initialize and load models from disk."""
        self.models_dir = models_dir or settings.models_path
        self.models: Dict[str, Dict] = {}
        self.metrics: Dict = {}
        self.metadata: Dict = {}
        self.load_models()
    
    def load_models(self) -> None:
        """Load all models from disk."""
        if not self.models_dir.exists():
            logger.warning(f"Models directory not found: {self.models_dir}")
            return
        
        for model_file in self.models_dir.glob("*.joblib"):
            if model_file.name in ['metrics.joblib', 'metadata.joblib']:
                continue
            
            model_key = model_file.stem
            try:
                self.models[model_key] = joblib.load(model_file)
                logger.info(f"Loaded model: {model_key}")
            except Exception as e:
                logger.error(f"Error loading model {model_key}: {e}")
        
        metrics_file = self.models_dir / "metrics.joblib"
        if metrics_file.exists():
            self.metrics = joblib.load(metrics_file)
        
        metadata_file = self.models_dir / "metadata.joblib"
        if metadata_file.exists():
            self.metadata = joblib.load(metadata_file)
        
        logger.info(f"Loaded {len(self.models)} models")
    
    def get_model_key(self, warehouse_id: str, product_category: str) -> str:
        """Generate model key from warehouse and category."""
        return f"{warehouse_id}_{product_category}"
    
    def predict(
        self, 
        warehouse_id: str, 
        product_category: str,
        horizon_days: int = None
    ) -> Optional[pd.DataFrame]:
        """Generate forecast for a specific warehouse and product category."""
        model_key = self.get_model_key(warehouse_id, product_category)
        
        if model_key not in self.models:
            logger.warning(f"Model not found for {model_key}")
            return None
        
        if horizon_days is None:
            horizon_days = settings.forecast_horizon_days
        
        model_info = self.models[model_key]
        fitted_model = model_info['fitted_model']
        
        try:
            # Generate forecast
            forecast_result = fitted_model.forecast(steps=horizon_days)
            
            # Create date range for forecast
            last_date = model_info['last_date']
            forecast_dates = pd.date_range(
                start=last_date + timedelta(days=1),
                periods=horizon_days,
                freq='D'
            )
            
            # Create forecast dataframe
            forecast = pd.DataFrame({
                'date': forecast_dates,
                'predicted_demand': forecast_result.values,
                'lower_bound': forecast_result.values * 0.8,
                'upper_bound': forecast_result.values * 1.2
            })
            
            # Ensure non-negative predictions
            forecast['predicted_demand'] = forecast['predicted_demand'].clip(lower=0)
            forecast['lower_bound'] = forecast['lower_bound'].clip(lower=0)
            forecast['upper_bound'] = forecast['upper_bound'].clip(lower=0)
            
            return forecast
            
        except Exception as e:
            logger.error(f"Error generating forecast for {model_key}: {e}")
            return None
    
    def get_reorder_alerts(
        self,
        inventory_df: pd.DataFrame,
        horizon_days: int = None
    ) -> List[Dict]:
        """Generate reorder alerts based on forecasts and inventory levels."""
        if horizon_days is None:
            horizon_days = settings.forecast_horizon_days
        
        alerts = []
        
        for _, row in inventory_df.iterrows():
            warehouse_id = row['Warehouse_ID']
            category = row['Product_Category']
            current_stock = row['Current_Stock_Units']
            reorder_level = row['Reorder_Level']
            
            forecast = self.predict(warehouse_id, category, horizon_days)
            
            if forecast is None:
                continue
            
            forecast['cumulative_demand'] = forecast['predicted_demand'].cumsum()
            forecast['remaining_stock'] = current_stock - forecast['cumulative_demand']
            
            breach_rows = forecast[forecast['remaining_stock'] < reorder_level]
            
            if not breach_rows.empty:
                first_breach = breach_rows.iloc[0]
                days_until_breach = (first_breach['date'] - datetime.now()).days
                
                alerts.append({
                    'warehouse_id': warehouse_id,
                    'product_category': category,
                    'current_stock': float(current_stock),
                    'reorder_level': float(reorder_level),
                    'predicted_stockout_date': first_breach['date'].isoformat(),
                    'days_until_stockout': int(days_until_breach),
                    'predicted_daily_demand': float(forecast['predicted_demand'].mean()),
                    'total_predicted_demand': float(forecast['predicted_demand'].sum()),
                    'severity': 'high' if days_until_breach <= 7 else 'medium' if days_until_breach <= 14 else 'low'
                })
        
        alerts.sort(key=lambda x: x['days_until_stockout'])
        return alerts
    
    def get_available_models(self) -> List[Dict]:
        """Get list of available models with their metrics."""
        models_info = []
        
        for model_key in self.models.keys():
            parts = model_key.split('_')
            warehouse_id = '_'.join(parts[:-1]) if len(parts) > 1 else model_key
            product_category = parts[-1] if len(parts) > 1 else 'Unknown'
            
            info = {
                'model_key': model_key,
                'warehouse_id': warehouse_id,
                'product_category': product_category
            }
            
            if model_key in self.metrics:
                info.update(self.metrics[model_key])
            
            models_info.append(info)
        
        return models_info
