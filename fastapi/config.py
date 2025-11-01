"""Configuration management for the ML service."""
from pydantic_settings import BaseSettings
from pathlib import Path


class Settings(BaseSettings):
    """Application settings loaded from environment variables."""
    
    # App
    app_name: str = "PIMS ML Service"
    app_env: str = "development"
    debug: bool = True
    
    # Server
    host: str = "0.0.0.0"
    port: int = 8001
    
    # Paths
    data_dir: str = "../backend"
    models_dir: str = "./models"
    logs_dir: str = "./logs"
    
    # Laravel API
    laravel_api_url: str = "http://127.0.0.1:8000/api"
    
    # CORS
    cors_origins: str = "http://localhost:5173,http://127.0.0.1:5173"
    
    # Model settings
    forecast_horizon_days: int = 14
    min_training_samples: int = 30
    
    class Config:
        env_file = ".env"
        case_sensitive = False
    
    @property
    def cors_origins_list(self) -> list:
        """Return CORS origins as a list."""
        return [origin.strip() for origin in self.cors_origins.split(",")]
    
    @property
    def data_path(self) -> Path:
        """Return Path object for data directory."""
        return Path(self.data_dir)
    
    @property
    def models_path(self) -> Path:
        """Return Path object for models directory."""
        path = Path(self.models_dir)
        path.mkdir(parents=True, exist_ok=True)
        return path
    
    @property
    def logs_path(self) -> Path:
        """Return Path object for logs directory."""
        path = Path(self.logs_dir)
        path.mkdir(parents=True, exist_ok=True)
        return path


settings = Settings()
