# Cart and Stock Management System Documentation

## Overview
The cart and stock management system provides a complete e-commerce solution with real-time inventory tracking, session-based cart management, and transaction-safe stock updates.

## Core Components

### 1. Cart Handler (`handlers/cart.handler.php`)
**Purpose**: Manages all cart operations with integrated stock management
**Authentication Required**: Yes (customer accounts only)

#### Available Actions:
- `add` - Add products to cart
- `update` - Update quantity of items in cart
- `remove` - Remove items from cart
- `clear` - Empty entire cart
- `get` - Retrieve cart contents
- `get_stock` - Get current stock for a product

#### Error Handling:
- Uses `ErrorHandler` utility for consistent error responses
- All database operations are wrapped in transactions
- Proper HTTP status codes for different error types

### 2. Cart Utility (`utils/cart.util.php`)
**Purpose**: Helper functions for cart calculations and management

#### Key Functions:
- `calculateCartTotals()` - Calculate subtotal, tax, shipping
- `getCartCount()` - Get number of items in cart
- `getItemQuantityInCart()` - Get quantity of specific item
- `logCartActivity()` - Log cart operations for debugging

### 3. Stock Management Features

#### Real-Time Stock Tracking:
- Stock is reserved when items are added to cart
- Stock is restored when items are removed or quantities decreased
- All stock operations use database transactions for consistency

#### Stock Validation:
- Prevents adding more items than available in stock
- Provides clear error messages for insufficient stock
- Updates product stock in real-time during cart operations

## Detailed Functionality

### Adding Items to Cart

```php
// POST to handlers/cart.handler.php
// Required parameters:
{
    "action": "add",
    "product_id": "uuid",
    "quantity": 1
}
```

**Process Flow:**
1. Validate user authentication
2. Check product exists and is active
3. Verify sufficient stock available
4. Start database transaction
5. Reserve stock by updating product quantity
6. Add item to session cart
7. Commit transaction
8. Return success response with updated cart info

**Error Scenarios:**
- Product not found or inactive
- Insufficient stock
- Database transaction failures
- Invalid quantity values

### Updating Cart Quantities

```php
// POST to handlers/cart.handler.php
// Required parameters:
{
    "action": "update",
    "product_id": "uuid",
    "quantity": 3
}
```

**Process Flow:**
1. Calculate difference between current and new quantity
2. If increasing: Check if enough stock available
3. If decreasing: Restore stock to product
4. Update cart session data
5. Return updated cart summary

**Special Cases:**
- Setting quantity to 0 removes the item completely
- Handles both stock increases and decreases properly
- Maintains cart session integrity

### Removing Items from Cart

```php
// POST to handlers/cart.handler.php
// Required parameters:
{
    "action": "remove",
    "product_id": "uuid"
}
```

**Process Flow:**
1. Find item quantity in cart
2. Restore all reserved stock to product
3. Remove item from session cart
4. Re-index cart array
5. Return updated cart status

### Clearing Entire Cart

```php
// POST to handlers/cart.handler.php
// Required parameters:
{
    "action": "clear"
}
```

**Process Flow:**
1. Iterate through all cart items
2. Restore stock for each item
3. Clear session cart array
4. Return empty cart confirmation

### Getting Cart Contents

```php
// GET/POST to handlers/cart.handler.php
// Required parameters:
{
    "action": "get"
}
```

**Returns:**
- Complete cart items with product details
- Current cart totals (subtotal, tax, shipping)
- Item count and availability status

## Stock Management Features

### Transaction Safety
- All stock operations use database transactions
- Rollback on any failure ensures data consistency
- FOR UPDATE locks prevent race conditions

### Stock Reservation Model
- Stock is immediately reserved when added to cart
- Reserved stock is not available to other customers
- Stock is restored when cart is modified or cleared

### Admin Stock Management
Integration with admin panel (`handlers/admin.handler.php`):
- Direct stock updates
- Bulk stock adjustments
- Stock history tracking

## Database Schema Integration

### Products Table Fields:
- `stock_quantity` - Current available stock
- `is_active` - Product availability flag
- `updated_at` - Last modification timestamp

### Orders Integration:
- Stock is permanently deducted during checkout
- Order items track quantities sold
- Failed orders restore stock automatically

## Session Management

### Cart Storage:
```php
$_SESSION['cart'] = [
    [
        'id' => 'product_uuid',
        'product_name' => 'Product Name',
        'product_sku' => 'SKU123',
        'price' => 29.99,
        'quantity' => 2,
        'category' => 'Electronics',
        'description' => 'Product description',
        'image' => 'product.jpg'
    ]
];
```

### Session Security:
- Cart is tied to authenticated user sessions
- Automatic cleanup on logout
- Session-based cart persistence

## Error Handling

### Error Types Handled:
1. **Authentication Errors** (401)
   - User not logged in
   - Invalid session

2. **Validation Errors** (400)
   - Invalid product IDs
   - Invalid quantities
   - Missing required parameters

3. **Not Found Errors** (404)
   - Product not found
   - Empty cart operations
   - Item not in cart

4. **Stock Errors** (400)
   - Insufficient stock
   - Product inactive

5. **Server Errors** (500)
   - Database connection failures
   - Transaction rollback scenarios

### Error Response Format:
```json
{
    "success": false,
    "message": "Error description",
    "code": 400
}
```

## Integration Points

### Checkout Process (`handlers/checkout.handler.php`)
- Validates cart contents before order creation
- Permanently deducts stock during order placement
- Clears cart after successful order

### Admin Interface (`pages/Admin/index.php`)
- Real-time stock monitoring
- Stock adjustment capabilities
- Product management integration

### Authentication System
- Requires customer account for cart access
- Integrates with session management
- Supports both user and customer account types

## Performance Considerations

### Database Optimization:
- Uses prepared statements for all queries
- Minimal database queries per operation
- Efficient stock checking with FOR UPDATE locks

### Session Optimization:
- Cart data stored in session for fast access
- Minimal session data to reduce memory usage
- Proper session cleanup

## Security Features

### Input Validation:
- All inputs sanitized and validated
- Type checking for quantities and IDs
- SQL injection prevention with prepared statements

### Transaction Safety:
- Database transactions prevent data corruption
- Proper error handling and rollback
- Race condition prevention

## Monitoring and Logging

### Activity Logging:
- All cart operations logged for debugging
- Error logging for troubleshooting
- User activity tracking

### Debug Information:
- Detailed error messages in logs
- Transaction status tracking
- Stock level monitoring

## Configuration

### Environment Variables:
- Database connection settings via `envSetter.util.php`
- Configurable tax rates and shipping rules
- Error handling configuration

### Customization Points:
- Tax calculation logic in `calculateCartTotals()`
- Shipping rules and thresholds
- Stock reservation policies

## Testing and Validation

### Recommended Testing Scenarios:
1. **Concurrent Access**: Multiple users adding same product
2. **Stock Depletion**: Adding more items than available
3. **Transaction Failures**: Database connection issues
4. **Session Management**: Cart persistence across sessions
5. **Error Recovery**: Proper rollback on failures

### Validation Checks:
- Stock consistency after operations
- Cart total calculations
- Session data integrity
- Error response formats

## Troubleshooting

### Common Issues:
1. **Stock Inconsistencies**: Check transaction rollback logs
2. **Cart Not Updating**: Verify session configuration
3. **Stock Overselling**: Review FOR UPDATE lock usage
4. **Performance Issues**: Check database query efficiency

### Debug Tools:
- Cart activity logs in error_log
- Database query logging
- Session data inspection
- Error handler utility responses
