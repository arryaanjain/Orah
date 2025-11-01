"""Data loading and preprocessing for demand forecasting."""
import pandas as pd
import numpy as np
from pathlib import Path
from typing import Dict, List, Tuple
from datetime import datetime, timedelta
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class DataProcessor:
    """Handle data loading and preprocessing for demand forecasting."""
    
    def __init__(self, data_dir: Path):
        """Initialize with data directory path."""
        self.data_dir = data_dir
        self.orders_df = None
        self.inventory_df = None
        self.warehouse_mapping = {}
        
    def load_data(self) -> Tuple[pd.DataFrame, pd.DataFrame]:
        """Load orders and warehouse inventory CSVs."""
        orders_path = self.data_dir / "orders.csv"
        inventory_path = self.data_dir / "warehouse_inventory.csv"
        
        if not orders_path.exists():
            raise FileNotFoundError(f"Orders file not found: {orders_path}")
        if not inventory_path.exists():
            raise FileNotFoundError(f"Inventory file not found: {inventory_path}")
        
        logger.info(f"Loading orders from {orders_path}")
        self.orders_df = pd.read_csv(orders_path)
        self.orders_df['Order_Date'] = pd.to_datetime(self.orders_df['Order_Date'])
        
        logger.info(f"Loading inventory from {inventory_path}")
        self.inventory_df = pd.read_csv(inventory_path)
        self.inventory_df['Last_Restocked_Date'] = pd.to_datetime(
            self.inventory_df['Last_Restocked_Date']
        )
        
        # Create warehouse mapping (Location -> Warehouse_ID)
        self.warehouse_mapping = dict(
            zip(self.inventory_df['Location'], self.inventory_df['Warehouse_ID'])
        )
        
        logger.info(f"Loaded {len(self.orders_df)} orders and {len(self.inventory_df)} inventory records")
        return self.orders_df, self.inventory_df
    
    def get_warehouse_from_location(self, location: str) -> str:
        """Map location to warehouse ID."""
        return self.warehouse_mapping.get(location, f"WH_Unknown_{location}")
    
    def aggregate_daily_demand(self) -> pd.DataFrame:
        """
        Aggregate orders to daily demand by warehouse and product category.
        
        Returns:
            DataFrame with columns: date, warehouse_id, product_category, 
                                   order_count, total_value
        """
        if self.orders_df is None:
            self.load_data()
        
        # Map origin to warehouse
        self.orders_df['warehouse_id'] = self.orders_df['Origin'].map(
            self.get_warehouse_from_location
        )
        
        # Aggregate by date, warehouse, and product category
        daily_demand = self.orders_df.groupby([
            pd.Grouper(key='Order_Date', freq='D'),
            'warehouse_id',
            'Product_Category'
        ]).agg({
            'Order_ID': 'count',  # Count of orders
            'Order_Value_INR': 'sum'  # Total value
        }).reset_index()
        
        daily_demand.columns = [
            'date', 'warehouse_id', 'product_category', 
            'order_count', 'total_value'
        ]
        
        logger.info(f"Aggregated to {len(daily_demand)} daily demand records")
        return daily_demand
    
    def prepare_training_data(
        self, 
        warehouse_id: str = None,
        product_category: str = None
    ) -> Dict[str, pd.DataFrame]:
        """
        Prepare training datasets for Prophet.
        
        Args:
            warehouse_id: Filter by specific warehouse (None = all)
            product_category: Filter by specific category (None = all)
        
        Returns:
            Dict mapping (warehouse, category) -> DataFrame with 'ds' and 'y' columns
        """
        daily_demand = self.aggregate_daily_demand()
        
        # Filter if specified
        if warehouse_id:
            daily_demand = daily_demand[daily_demand['warehouse_id'] == warehouse_id]
        if product_category:
            daily_demand = daily_demand[daily_demand['product_category'] == product_category]
        
        # Create complete date range (fill missing dates with 0)
        min_date = daily_demand['date'].min()
        max_date = daily_demand['date'].max()
        date_range = pd.date_range(start=min_date, end=max_date, freq='D')
        
        training_datasets = {}
        
        # Group by warehouse and category
        for (wh_id, category), group in daily_demand.groupby(['warehouse_id', 'product_category']):
            # Create complete date range
            complete_df = pd.DataFrame({'date': date_range})
            
            # Merge with actual data
            merged = complete_df.merge(
                group[['date', 'order_count', 'total_value']], 
                on='date', 
                how='left'
            )
            
            # Fill missing values with 0
            merged['order_count'] = merged['order_count'].fillna(0)
            merged['total_value'] = merged['total_value'].fillna(0)
            
            # Format for Prophet (ds = datestamp, y = target variable)
            prophet_df = pd.DataFrame({
                'ds': merged['date'],
                'y': merged['order_count']  # Predict order count
            })
            
            training_datasets[f"{wh_id}_{category}"] = prophet_df
        
        logger.info(f"Prepared {len(training_datasets)} training datasets")
        return training_datasets
    
    def get_inventory_context(self) -> pd.DataFrame:
        """
        Get current inventory levels and reorder points.
        
        Returns:
            DataFrame with warehouse_id, product_category, current_stock, reorder_level
        """
        if self.inventory_df is None:
            self.load_data()
        
        return self.inventory_df[[
            'Warehouse_ID', 'Location', 'Product_Category', 
            'Current_Stock_Units', 'Reorder_Level', 'Storage_Cost_per_Unit',
            'Last_Restocked_Date'
        ]].copy()
    
    def get_available_warehouses(self) -> List[str]:
        """Get list of unique warehouse IDs."""
        if self.inventory_df is None:
            self.load_data()
        return sorted(self.inventory_df['Warehouse_ID'].unique().tolist())
    
    def get_available_categories(self) -> List[str]:
        """Get list of unique product categories."""
        if self.orders_df is None:
            self.load_data()
        return sorted(self.orders_df['Product_Category'].unique().tolist())
