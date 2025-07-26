    # Cart Functionality & Stock Management Documentation

    ## Overview
    The SINTHESIZE Cart System provides comprehensive e-commerce functionality with real-time stock management, secure checkout processing, and seamless user experience across shop and cart pages.

    ## Core Features

    ### 1. Shopping Cart Management
    - **Add to Cart**: Products can be added from the shop page with quantity selection
    - **Update Quantities**: Real-time quantity adjustments with stock validation
    - **Remove Items**: Individual item removal or complete cart clearing
    - **Persistent Sessions**: Cart contents maintained across user sessions
    - **Stock Validation**: Real-time stock checking prevents overselling

    ### 2. Stock Management System
    - **Database-Driven**: All stock quantities stored in PostgreSQL database
    - **Real-Time Updates**: Stock decreases when items added to cart
    - **Concurrent Protection**: Database transactions prevent race conditions
    - **Stock Restoration**: Quantities restored when items removed from cart
    - **Low Stock Indicators**: Visual indicators for limited availability

    ### 3. Checkout Process
    - **Customer Validation**: Ensures only registered customers can checkout
    - **Order Creation**: Generates unique order numbers and records
    - **Transaction Safety**: Database transactions ensure data consistency
    - **Cart Clearing**: Automatic cart clearing after successful checkout
    - **Error Handling**: Comprehensive error management and user feedback

    ## Technical Implementation

    ### File Structure
    ```
    handlers/
    ├── cart.handler.php          # Main cart operations
    └── checkout.handler.php      # Checkout processing

    pages/
    ├── Cart/index.php            # Cart display page
    └── Shop/index.php            # Product catalog with add-to-cart

    utils/
    └── cart.util.php             # Cart utility functions

    assets/css/
    ├── cart.css                  # Cart page styling
    └── shop.css                  # Shop page styling
    ```

    ### Database Schema

    #### Products Table
    ```sql
    CREATE TABLE products (
        id UUID PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        sku VARCHAR(100) UNIQUE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INTEGER NOT NULL DEFAULT 0,
        category VARCHAR(100),
        description TEXT,
        image_url TEXT,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ```

    #### Orders Table
    ```sql
    CREATE TABLE orders (
        id UUID PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_id UUID NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(8,2) DEFAULT 0,
        shipping_amount DECIMAL(8,2) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id)
    );
    ```

    #### Order Items Table
    ```sql
    CREATE TABLE order_items (
        id UUID PRIMARY KEY,
        order_id UUID NOT NULL,
        product_id UUID NOT NULL,
        quantity INTEGER NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    );
    ```

    ## API Endpoints

    ### Cart Handler (cart.handler.php)

    #### Add to Cart
    ```php
    POST /handlers/cart.handler.php
    {
        "action": "add",
        "product_id": "uuid",
        "quantity": 1
    }
    ```

    **Response:**
    ```json
    {
        "success": true,
        "message": "Product added to cart successfully",
        "cart_count": 3,
        "cart_summary": {
            "subtotal": "150.00",
            "tax": "12.00",
            "shipping": "15.99",
            "total": "177.99"
        },
        "new_stock": 45
    }
    ```

    #### Update Quantity
    ```php
    POST /handlers/cart.handler.php
    {
        "action": "update",
        "product_id": "uuid",
        "quantity": 2
    }
    ```

    #### Remove Item
    ```php
    POST /handlers/cart.handler.php
    {
        "action": "remove",
        "product_id": "uuid"
    }
    ```

    #### Clear Cart
    ```php
    POST /handlers/cart.handler.php
    {
        "action": "clear"
    }
    ```

    ### Checkout Handler (checkout.handler.php)

    #### Process Checkout
    ```php
    POST /handlers/checkout.handler.php
    {
        "action": "checkout"
    }
    ```

    **Response:**
    ```json
    {
        "success": true,
        "message": "Order placed successfully",
        "order_number": "ORD-20250126-001",
        "total_amount": "177.99",
        "order_id": "uuid"
    }
    ```


    ---

    *Last Updated: January 26, 2025*
    *Version: 1.0*