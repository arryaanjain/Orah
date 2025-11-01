# ML Analytics Integration - Testing Guide

## Overview
ML analytics service integrated with Laravel backend proxy and React frontend with **graceful degradation**.

## Architecture

```
React Frontend (port 5173)
    ↓
Laravel API Proxy (port 8000)
    ↓
FastAPI ML Service (port 8001)
```

### Graceful Degradation
- **ML Service Available**: Shows AI-powered reorder alerts with predictions
- **ML Service Unavailable**: Shows "Analytics Unavailable" message, basic dashboard still works

---

## Backend Setup (Already Complete)

### Files Created/Modified:

1. **AnalyticsService.php** - `/opt/lampp/htdocs/PIMS/backend/app/Services/AnalyticsService.php`
   - HTTP client for FastAPI communication
   - Health checking with 5-second timeout
   - Returns `null` when service unavailable

2. **AnalyticsController.php** - `/opt/lampp/htdocs/PIMS/backend/app/Http/Controllers/Api/AnalyticsController.php`
   - 7 endpoints: status, forecast, reorder-alerts, models, warehouses, categories, retrain
   - Returns `available: false` with empty data when ML service down (200 status for UX)

3. **routes/api.php** - Added analytics routes:
   ```php
   Route::prefix('analytics')->group(function () {
       Route::get('/status', [AnalyticsController::class, 'status']);
       Route::post('/forecast', [AnalyticsController::class, 'getForecast']);
       Route::get('/reorder-alerts', [AnalyticsController::class, 'getReorderAlerts']);
       Route::get('/models', [AnalyticsController::class, 'getModels']);
       Route::get('/warehouses', [AnalyticsController::class, 'getWarehouses']);
       Route::get('/categories', [AnalyticsController::class, 'getCategories']);
       Route::post('/retrain', [AnalyticsController::class, 'retrain']);
   });
   ```

4. **.env** - Added:
   ```
   ML_SERVICE_URL=http://127.0.0.1:8001
   ```

---

## Frontend Updates (Just Completed)

### Dashboard.tsx Enhanced
- **File**: `/opt/lampp/htdocs/PIMS/frontend/src/pages/Dashboard.tsx`
- **Features**:
  - ML Analytics status banner (green = available, yellow = unavailable)
  - Reorder alerts table with severity badges (high/medium/low)
  - Graceful degradation UI
  - Auto-checks analytics availability on mount

### Status Indicators:
- **Loading**: Gray spinner - "Checking service status..."
- **Available**: Green check - "Advanced forecasting and reorder alerts active"
- **Unavailable**: Yellow X - "Service unavailable - Using basic inventory tracking"

---

## Testing Steps

### Test 1: ML Service Unavailable (Graceful Degradation)

1. **Ensure ML service is NOT running**
   ```bash
   # Don't start FastAPI yet
   ```

2. **Start Laravel backend**
   ```bash
   cd /opt/lampp/htdocs/PIMS/backend
   php artisan serve
   ```

3. **Start React frontend**
   ```bash
   cd /opt/lampp/htdocs/PIMS/frontend
   npm run dev
   ```

4. **Navigate to Dashboard**
   - Login with Google OAuth
   - Go to dashboard page
   - **Expected**: Yellow banner showing "Analytics Unavailable"
   - **Expected**: Instruction box with command to start service
   - **Expected**: Basic dashboard still works (stats, quick actions, etc.)

### Test 2: ML Service Available (Full Features)

1. **Start FastAPI ML service**
   ```bash
   cd /opt/lampp/htdocs/PIMS/fastapi
   python main.py
   ```
   Expected output:
   ```
   INFO:     Uvicorn running on http://127.0.0.1:8001
   ```

2. **Refresh Dashboard**
   - **Expected**: Green banner showing "Analytics Available"
   - **Expected**: Reorder alerts table appears
   - **Expected**: Alerts sorted by severity (high → medium → low)

### Test 3: Logout Functionality

1. **Click user avatar dropdown** (top-right corner)
2. **Click "Logout" button**
   - **Expected**: Calls `/api/auth/logout`
   - **Expected**: Redirects to `/login`
   - **Expected**: Token and user data cleared from localStorage

---

## API Endpoints

### Analytics Endpoints (All require Sanctum auth)

| Method | Endpoint | Purpose | Returns When ML Down |
|--------|----------|---------|---------------------|
| GET | `/api/analytics/status` | Check ML service availability | `{available: false}` |
| POST | `/api/analytics/forecast` | Get demand forecast | `{available: false, data: []}` |
| GET | `/api/analytics/reorder-alerts` | Get stockout predictions | `{available: false, data: {alerts: []}}` |
| GET | `/api/analytics/models` | List trained models | `{available: false, data: []}` |
| GET | `/api/analytics/warehouses` | Get warehouse list | `{available: false, data: []}` |
| GET | `/api/analytics/categories` | Get category list | `{available: false, data: []}` |
| POST | `/api/analytics/retrain` | Retrain ML models | `{available: false}` |

---

## ML Service Details

### Models Trained
- **Count**: 53 ARIMA/SARIMAX models
- **Size**: ~71MB total
- **Location**: `/opt/lampp/htdocs/PIMS/fastapi/models/`
- **Format**: Joblib serialized (.joblib files)

### Training Metrics
- Stored in `models/metrics.joblib`
- Contains MAE and RMSE for each model
- Example:
  ```json
  {
    "WH001_Mumbai_Books": {
      "mae": 15.32,
      "rmse": 21.45,
      "trained_at": "2025-06-10T12:34:56"
    }
  }
  ```

### Reorder Alert Logic
- Forecasts next 14 days of demand
- Calculates predicted stockout date
- Severity levels:
  - **HIGH**: < 3 days until stockout
  - **MEDIUM**: 3-7 days until stockout
  - **LOW**: 7-14 days until stockout

---

## Troubleshooting

### Issue: "Analytics Unavailable" even when FastAPI is running

**Solutions**:
1. Check FastAPI is on port 8001:
   ```bash
   curl http://127.0.0.1:8001/health
   ```
   Should return: `{"status": "healthy"}`

2. Check Laravel .env has correct URL:
   ```
   ML_SERVICE_URL=http://127.0.0.1:8001
   ```

3. Check Laravel logs:
   ```bash
   tail -f /opt/lampp/htdocs/PIMS/backend/storage/logs/laravel.log
   ```

### Issue: 401 Unauthorized on analytics endpoints

**Cause**: Missing or expired Sanctum token

**Solution**:
1. Login again
2. Check localStorage has `token` key
3. Verify token in request headers (Network tab)

### Issue: Empty alerts array when ML service is running

**Possible Reasons**:
1. **Good news**: No items predicted to stockout in next 14 days
2. **Data issue**: No inventory data loaded
3. **Models issue**: Models need retraining

**Check**:
```bash
# Call FastAPI directly
curl http://127.0.0.1:8001/reorder-alerts
```

---

## Next Steps (Optional Enhancements)

### 1. Forecast Charts
- Add recharts/chart.js to visualize demand forecasts
- Create dedicated "Analytics" page with detailed charts

### 2. Model Monitoring
- Display model accuracy metrics on frontend
- Show last training date
- Add "Retrain Models" button

### 3. Real-time Updates
- WebSocket connection for live alerts
- Auto-refresh when new data arrives

### 4. Notifications
- Browser notifications for critical alerts
- Email alerts for high-severity stockouts

### 5. Dockerization
- Docker Compose for all services
- Easier deployment and scaling

### 6. Scheduled Retraining
- Laravel queue job to retrain models weekly
- Automatic retraining when new data arrives

---

## File Structure Summary

```
/opt/lampp/htdocs/PIMS/
├── backend/
│   ├── app/
│   │   ├── Services/
│   │   │   └── AnalyticsService.php         # ML service HTTP client
│   │   └── Http/Controllers/Api/
│   │       └── AnalyticsController.php      # Analytics API endpoints
│   ├── routes/
│   │   └── api.php                          # Added analytics routes
│   └── .env                                 # ML_SERVICE_URL config
├── frontend/
│   └── src/
│       ├── pages/
│       │   └── Dashboard.tsx                # Updated with ML analytics UI
│       └── services/
│           └── api.ts                       # HTTP client (already set up)
└── fastapi/
    ├── main.py                              # FastAPI app (7 endpoints)
    ├── train_forecast.py                    # ARIMA/SARIMAX training
    ├── forecast_service.py                  # Prediction service
    ├── data_processor.py                    # Data loading & aggregation
    └── models/                              # 53 trained .joblib models
```

---

## Success Criteria

✅ **With ML Service Running**:
- Green status banner
- Reorder alerts table populated
- Severity badges (HIGH/MEDIUM/LOW)
- Proper date formatting

✅ **With ML Service Stopped**:
- Yellow status banner
- "Analytics Unavailable" message
- Instructions to start service
- Basic dashboard still functional

✅ **Logout**:
- Calls `/api/auth/logout`
- Clears localStorage
- Redirects to login

---

## Commands Cheat Sheet

```bash
# Start Laravel backend
cd /opt/lampp/htdocs/PIMS/backend && php artisan serve

# Start React frontend
cd /opt/lampp/htdocs/PIMS/frontend && npm run dev

# Start FastAPI ML service
cd /opt/lampp/htdocs/PIMS/fastapi && python main.py

# Test ML service health
curl http://127.0.0.1:8001/health

# Test reorder alerts (directly)
curl http://127.0.0.1:8001/reorder-alerts

# Test via Laravel proxy (requires auth token)
curl -H "Authorization: Bearer YOUR_TOKEN" http://127.0.0.1:8000/api/analytics/status
```

---

**Integration Status**: ✅ COMPLETE  
**Graceful Degradation**: ✅ IMPLEMENTED  
**Ready for Testing**: ✅ YES
