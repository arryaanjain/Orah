"""FastAPI application for ML forecasting service."""
from fastapi import FastAPI, HTTPException, Depends, Header
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from typing import List, Optional
import pandas as pd
from datetime import datetime
import httpx

from config import settings
from data_processor import DataProcessor
from forecast_service import ForecastService

# Initialize FastAPI app
app = FastAPI(
    title=settings.app_name,
    description="ML-powered demand forecasting and inventory optimization",
    version="1.0.0",
    debug=settings.debug
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins_list,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize services
data_processor = DataProcessor(settings.data_path)
forecast_service = ForecastService()


# === Pydantic Models ===

class ForecastRequest(BaseModel):
    """Request model for forecast prediction."""
    warehouse_id: str = Field(..., description="Warehouse identifier")
    product_category: str = Field(..., description="Product category")
    horizon_days: Optional[int] = Field(None, description="Number of days to forecast")


class ForecastResponse(BaseModel):
    """Response model for forecast prediction."""
    warehouse_id: str
    product_category: str
    forecast: List[dict]
    horizon_days: int
    generated_at: str


class ReorderAlert(BaseModel):
    """Model for reorder alerts."""
    warehouse_id: str
    product_category: str
    current_stock: float
    reorder_level: float
    predicted_stockout_date: str
    days_until_stockout: int
    predicted_daily_demand: float
    total_predicted_demand: float
    severity: str


class ReorderAlertsResponse(BaseModel):
    """Response model for reorder alerts."""
    alerts: List[ReorderAlert]
    total_alerts: int
    generated_at: str


class ModelInfo(BaseModel):
    """Model information."""
    model_key: str
    warehouse_id: str
    product_category: str
    mae: Optional[float] = None
    rmse: Optional[float] = None
    training_samples: Optional[int] = None
    trained_at: Optional[str] = None


class ModelsListResponse(BaseModel):
    """Response for available models."""
    models: List[ModelInfo]
    total_models: int


# === Authentication ===

async def verify_token(authorization: Optional[str] = Header(None)):
    """
    Verify Bearer token by calling Laravel API.
    For now, this is optional - you can enable strict auth later.
    """
    if not authorization:
        # For development, allow unauthenticated requests
        if settings.app_env == "development":
            return None
        raise HTTPException(status_code=401, detail="Authorization header missing")
    
    if not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Invalid authorization header")
    
    token = authorization.split(" ")[1]
    
    # Verify token with Laravel API
    try:
        async with httpx.AsyncClient() as client:
            response = await client.get(
                f"{settings.laravel_api_url}/user",
                headers={"Authorization": f"Bearer {token}"}
            )
            
            if response.status_code != 200:
                raise HTTPException(status_code=401, detail="Invalid token")
            
            return response.json()
    except httpx.RequestError:
        # If Laravel is down, allow in development
        if settings.app_env == "development":
            return None
        raise HTTPException(status_code=503, detail="Authentication service unavailable")


# === API Endpoints ===

@app.get("/")
async def root():
    """Health check endpoint."""
    return {
        "service": settings.app_name,
        "status": "running",
        "version": "1.0.0",
        "models_loaded": len(forecast_service.models),
        "environment": settings.app_env
    }


@app.get("/health")
async def health_check():
    """Detailed health check."""
    return {
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "models_loaded": len(forecast_service.models),
        "data_available": data_processor.orders_df is not None
    }


@app.post("/forecast", response_model=ForecastResponse)
async def get_forecast(
    request: ForecastRequest,
    user = Depends(verify_token)
):
    """
    Get demand forecast for a specific warehouse and product category.
    """
    forecast = forecast_service.predict(
        warehouse_id=request.warehouse_id,
        product_category=request.product_category,
        horizon_days=request.horizon_days
    )
    
    if forecast is None:
        raise HTTPException(
            status_code=404,
            detail=f"No model found for {request.warehouse_id} - {request.product_category}"
        )
    
    return ForecastResponse(
        warehouse_id=request.warehouse_id,
        product_category=request.product_category,
        forecast=forecast.to_dict('records'),
        horizon_days=request.horizon_days or settings.forecast_horizon_days,
        generated_at=datetime.now().isoformat()
    )


@app.get("/reorder-alerts", response_model=ReorderAlertsResponse)
async def get_reorder_alerts(
    horizon_days: Optional[int] = None,
    user = Depends(verify_token)
):
    """
    Get reorder alerts for all warehouse-category combinations.
    """
    # Load current inventory
    inventory_df = data_processor.get_inventory_context()
    
    # Generate alerts
    alerts = forecast_service.get_reorder_alerts(
        inventory_df=inventory_df,
        horizon_days=horizon_days
    )
    
    return ReorderAlertsResponse(
        alerts=[ReorderAlert(**alert) for alert in alerts],
        total_alerts=len(alerts),
        generated_at=datetime.now().isoformat()
    )


@app.get("/models", response_model=ModelsListResponse)
async def list_models(user = Depends(verify_token)):
    """
    Get list of available trained models.
    """
    models = forecast_service.get_available_models()
    
    return ModelsListResponse(
        models=[ModelInfo(**model) for model in models],
        total_models=len(models)
    )


@app.get("/warehouses")
async def list_warehouses(user = Depends(verify_token)):
    """Get list of available warehouses."""
    data_processor.load_data()
    return {
        "warehouses": data_processor.get_available_warehouses()
    }


@app.get("/categories")
async def list_categories(user = Depends(verify_token)):
    """Get list of available product categories."""
    data_processor.load_data()
    return {
        "categories": data_processor.get_available_categories()
    }


@app.post("/retrain")
async def retrain_models(user = Depends(verify_token)):
    """
    Retrain all models with latest data.
    This is a long-running operation - consider using background tasks in production.
    """
    from train_forecast import ForecastTrainer
    
    # Reload data
    data_processor.load_data()
    
    # Train models
    trainer = ForecastTrainer(data_processor)
    trainer.train_all_models()
    trainer.save_models()
    
    # Reload models in forecast service
    forecast_service.load_models()
    
    return {
        "status": "success",
        "models_trained": len(trainer.models),
        "timestamp": datetime.now().isoformat()
    }


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app",
        host=settings.host,
        port=settings.port,
        reload=settings.debug
    )
