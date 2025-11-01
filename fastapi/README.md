# PIMS ML Service

Machine Learning service for demand forecasting and inventory optimization using Prophet time-series models.

## Features

- **Demand Forecasting**: Predict future demand for warehouse-product combinations
- **Reorder Alerts**: Identify when inventory will fall below reorder levels
- **Multi-warehouse Support**: Train separate models per warehouse and product category
- **REST API**: FastAPI-powered endpoints for integration

## Setup

### 1. Install Dependencies

```bash
cd /opt/lampp/htdocs/PIMS/fastapi
pip install -r requirements.txt
```

### 2. Configure Environment

Copy `.env.example` to `.env` (already done) and adjust if needed:

```bash
# .env is already configured with defaults
# Customize if needed
```

### 3. Train Models

Train forecasting models on historical data:

```bash
python train_forecast.py
```

This will:
- Load `orders.csv` and `warehouse_inventory.csv`
- Aggregate daily demand per warehouse/category
- Train Prophet models for each combination
- Save models to `./models/` directory
- Display training metrics (MAE, RMSE)

### 4. Start the API Server

```bash
python main.py
```

Or with uvicorn directly:

```bash
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

The API will be available at `http://localhost:8001`

## API Endpoints

### Health Check
```
GET /
GET /health
```

### Get Forecast
```
POST /forecast
Body: {
  "warehouse_id": "WH001_Mumbai",
  "product_category": "Electronics",
  "horizon_days": 14  # optional
}
```

### Get Reorder Alerts
```
GET /reorder-alerts?horizon_days=14
```

Returns list of items predicted to fall below reorder level.

### List Available Models
```
GET /models
```

### List Warehouses/Categories
```
GET /warehouses
GET /categories
```

### Retrain Models
```
POST /retrain
```

## Authentication

The service accepts Bearer tokens from Laravel API. In development mode, auth is optional.

To enable strict auth:
1. Set `APP_ENV=production` in `.env`
2. Ensure `LARAVEL_API_URL` points to your Laravel instance

## Model Details

- **Algorithm**: Facebook Prophet (time-series forecasting)
- **Training**: One model per (warehouse, product_category) combination
- **Horizon**: Default 14 days (configurable)
- **Metrics**: MAE (Mean Absolute Error), RMSE (Root Mean Square Error)
- **Seasonality**: Weekly and yearly patterns detected automatically

## File Structure

```
fastapi/
├── main.py                 # FastAPI application
├── train_forecast.py       # Model training script
├── forecast_service.py     # Prediction service
├── data_processor.py       # Data loading and preprocessing
├── config.py              # Configuration management
├── requirements.txt       # Python dependencies
├── .env                   # Environment variables
├── models/                # Trained models (auto-created)
└── logs/                  # Logs (auto-created)
```

## Usage Examples

### Python
```python
import httpx

# Get forecast
response = httpx.post("http://localhost:8001/forecast", json={
    "warehouse_id": "WH001_Mumbai",
    "product_category": "Electronics"
})
forecast = response.json()

# Get reorder alerts
alerts = httpx.get("http://localhost:8001/reorder-alerts").json()
```

### cURL
```bash
# Get forecast
curl -X POST http://localhost:8001/forecast \
  -H "Content-Type: application/json" \
  -d '{"warehouse_id":"WH001_Mumbai","product_category":"Electronics"}'

# Get reorder alerts
curl http://localhost:8001/reorder-alerts
```

## Troubleshooting

### Models not loading
- Run `python train_forecast.py` first
- Check `./models/` directory exists and contains `.joblib` files

### Import errors
- Ensure all dependencies installed: `pip install -r requirements.txt`
- Prophet requires additional system libraries on some platforms

### Data not found
- Ensure `orders.csv` and `warehouse_inventory.csv` are in `../backend/` directory
- Check `DATA_DIR` in `.env`

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Use a process manager (systemd, supervisor, or Docker)
3. Enable HTTPS with reverse proxy (nginx)
4. Schedule periodic retraining (cron or celery)
5. Add monitoring and alerting

## Next Steps

- Add more sophisticated features (holidays, promotions, weather)
- Implement ensemble methods (Prophet + LSTM)
- Add model versioning and A/B testing
- Integrate with inventory management system
- Build dashboards for visualization
