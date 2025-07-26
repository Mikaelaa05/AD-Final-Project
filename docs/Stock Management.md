## Stock Management Flow

### 1. Add to Cart Process
1. **Validate Product**: Check if product exists and is active
2. **Check Stock**: Verify sufficient quantity available
3. **Begin Transaction**: Start database transaction for consistency
4. **Update Stock**: Decrease product stock_quantity in database
5. **Update Session**: Add/update item in user's cart session
6. **Commit Transaction**: Finalize all changes
7. **Return Response**: Send success confirmation with updated data

### 2. Remove from Cart Process
1. **Validate Item**: Confirm item exists in cart
2. **Begin Transaction**: Start database transaction
3. **Restore Stock**: Increase product stock_quantity by removed amount
4. **Update Session**: Remove item from cart session
5. **Commit Transaction**: Finalize stock restoration
6. **Return Response**: Send confirmation

### 3. Checkout Process
1. **Validate Customer**: Ensure user is registered customer
2. **Validate Cart**: Check cart is not empty
3. **Generate Order**: Create unique order number and record
4. **Create Order Items**: Record each cart item as order item
5. **Clear Cart**: Empty user's cart session
6. **Send Confirmation**: Return order details

## Session Management

### Cart Session Structure
```php
$_SESSION['cart'] = [
    [
        'id' => 'product_uuid',
        'product_name' => 'Cyber Eye Implant',
        'product_sku' => 'CYBEYE-001',
        'price' => 1500.00,
        'quantity' => 1,
        'category' => 'Cybernetic Implants',
        'description' => 'Advanced ocular enhancement...',
        'image' => 'image_filename.png'
    ],
    // ... more items
];
```

### Cart Calculations
```php
// Subtotal
$subtotal = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $_SESSION['cart']));

// Tax (8%)
$tax = $subtotal * 0.08;

// Shipping (Free over $200)
$shipping = $subtotal > 200 ? 0 : 15.99;

// Total
$total = $subtotal + $tax + $shipping;
```

## Security Features

### 1. Input Validation
- **Product ID Validation**: UUID format verification
- **Quantity Limits**: Min 1, Max 99 per item
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output escaping

### 2. Authentication
- **Session Verification**: User must be logged in
- **Customer Validation**: Only registered customers can checkout
- **CSRF Protection**: Form token validation (recommended)

### 3. Stock Protection
- **Database Locking**: FOR UPDATE locks prevent race conditions
- **Transaction Isolation**: Ensures data consistency
- **Rollback on Failure**: Automatic reversal of failed operations

## Error Handling

### Common Error Scenarios
1. **Insufficient Stock**: User tries to add more items than available
2. **Product Not Found**: Invalid product ID provided
3. **Empty Cart**: Checkout attempted with no items
4. **Database Failure**: Connection or query errors
5. **Invalid Customer**: Non-customer tries to checkout

### Error Response Format
```json
{
    "success": false,
    "message": "Detailed error description",
    "error_code": "INSUFFICIENT_STOCK",
    "details": {
        "requested": 5,
        "available": 3
    }
}
```

## Frontend Integration

### JavaScript Cart Functions
```javascript
// Add to cart
function addToCart(productId, quantity) {
    fetch('/handlers/cart.handler.php', {
        method: 'POST',
        body: new FormData(formElement)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartUI(data);
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    });
}

// Update quantity
function updateQuantity(productId, newQuantity) {
    // Similar AJAX call with update action
}

// Checkout process
function processCheckout() {
    // AJAX call to checkout handler
}
```

### Real-Time Updates
- **Stock Synchronization**: Cross-tab communication via localStorage
- **Cart Count Updates**: Dynamic cart badge updates
- **Price Calculations**: Live total recalculation
- **Notification System**: User feedback for all actions

## Performance Optimizations

### 1. Database Optimizations
- **Indexed Columns**: Product ID, SKU, and stock_quantity indexed
- **Connection Pooling**: Efficient database connection management
- **Query Optimization**: Minimal database calls per operation

### 2. Caching Strategy
- **Session Caching**: Cart data stored in PHP sessions
- **Static Assets**: CSS and JS files with versioning
- **Database Queries**: Prepared statement caching

### 3. Frontend Optimizations
- **Lazy Loading**: Deferred image loading on shop page
- **Debounced Inputs**: Quantity change debouncing
- **Efficient DOM Updates**: Minimal DOM manipulation

## Monitoring & Analytics

### Key Metrics
- **Cart Abandonment Rate**: Track incomplete checkouts
- **Average Order Value**: Monitor cart totals
- **Stock Turnover**: Product popularity tracking
- **Error Rates**: Failed operations monitoring

### Logging
```php
// Cart activity logging
error_log("Cart Action: {$action} | User: {$userId} | Product: {$productId} | Quantity: {$quantity}");

// Stock change logging
error_log("Stock Update: Product {$productId} | Old: {$oldStock} | New: {$newStock} | Reason: {$reason}");
```

## Testing Guidelines

### Unit Tests
- **Cart Operations**: Add, update, remove functionality
- **Stock Management**: Validation and update logic
- **Calculations**: Tax, shipping, and total calculations

### Integration Tests
- **Database Transactions**: Multi-step operation testing
- **Session Management**: Cross-request cart persistence
- **API Endpoints**: Complete request/response testing

### Load Testing
- **Concurrent Users**: Multiple users adding same product
- **High Traffic**: Peak shopping period simulation
- **Database Load**: Stock management under stress

## Future Enhancements

### Planned Features
1. **Wishlist Integration**: Save items for later
2. **Inventory Alerts**: Low stock notifications
3. **Bulk Operations**: Multi-item cart actions
4. **Price History**: Track price changes
5. **Recommendation Engine**: Suggested products
6. **Mobile App API**: RESTful endpoints for mobile
7. **Analytics Dashboard**: Admin stock monitoring
8. **Automated Reordering**: Low stock triggers

### Scalability Considerations
- **Database Sharding**: Product catalog distribution
- **Redis Caching**: High-performance cart sessions
- **CDN Integration**: Static asset delivery
- **Microservices**: Cart and inventory service separation
