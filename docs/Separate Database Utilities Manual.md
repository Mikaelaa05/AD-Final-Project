# Separate Database Management Utilities

## 📋 Overview
Individual database management utilities for three core databases: Users, Customers, and Products.

## 🏗️ Database System Structure

### 👥 Users Database
- **Schema**: `database/users.model.sql`
- **Data**: `staticData/dummies/users.staticData.php`
- **Fields**: id, first_name, middle_name, last_name, password, username, role

### 🏢 Customers Database  
- **Schema**: `database/customers.model.sql`
- **Data**: `staticData/dummies/customers.staticData.php`
- **Fields**: 20 fields including contact info, address, financial tracking, activity status

### 🛍️ Products Database
- **Schema**: `database/products.model.sql`
- **Data**: `staticData/dummies/products.staticData.php`
- **Fields**: id, name, description, category, price, cost, sku, stock_quantity, weight, is_active

## 🔧 Individual Database Utilities

### Users Database Commands
```bash
# Migration (Drop & Create)
docker exec adfinalproject-service php utils/usersTableMigrate.util.php

# Reset (Truncate Data)
docker exec adfinalproject-service php utils/usersTableReset.util.php

# Seeding (Populate Data)
docker exec adfinalproject-service php utils/usersTableSeeder.util.php

# Verification (Check Status)
docker exec adfinalproject-service php utils/usersTableVerify.util.php
```

### Customers Database Commands
```bash
# Migration (Drop & Create)
docker exec adfinalproject-service php utils/customersTableMigrate.util.php

# Reset (Truncate Data)
docker exec adfinalproject-service php utils/customersTableReset.util.php

# Seeding (Populate Data)
docker exec adfinalproject-service php utils/customersTableSeeder.util.php

# Verification (Check Status)
docker exec adfinalproject-service php utils/customersTableVerify.util.php
```

### Products Database Commands
```bash
# Migration (Drop & Create)
docker exec adfinalproject-service php utils/productsTableMigrate.util.php

# Reset (Truncate Data)
docker exec adfinalproject-service php utils/productsTableReset.util.php

# Seeding (Populate Data)
docker exec adfinalproject-service php utils/productsTableSeeder.util.php

# Verification (Check Status)
docker exec adfinalproject-service php utils/productsTableVerify.util.php
```

## 🚀 Quick Database Setup

### Individual Database Setup
```bash
# Users Database
docker exec adfinalproject-service php utils/usersTableMigrate.util.php
docker exec adfinalproject-service php utils/usersTableSeeder.util.php
docker exec adfinalproject-service php utils/usersTableVerify.util.php

# Customers Database
docker exec adfinalproject-service php utils/customersTableMigrate.util.php
docker exec adfinalproject-service php utils/customersTableSeeder.util.php
docker exec adfinalproject-service php utils/customersTableVerify.util.php

# Products Database
docker exec adfinalproject-service php utils/productsTableMigrate.util.php
docker exec adfinalproject-service php utils/productsTableSeeder.util.php
docker exec adfinalproject-service php utils/productsTableVerify.util.php
```

### Batch Database Setup (All Three)
```bash
# Full Migration
docker exec adfinalproject-service php utils/usersTableMigrate.util.php
docker exec adfinalproject-service php utils/customersTableMigrate.util.php
docker exec adfinalproject-service php utils/productsTableMigrate.util.php

# Full Seeding
docker exec adfinalproject-service php utils/usersTableSeeder.util.php
docker exec adfinalproject-service php utils/customersTableSeeder.util.php
docker exec adfinalproject-service php utils/productsTableSeeder.util.php

# Full Verification
docker exec adfinalproject-service php utils/usersTableVerify.util.php
docker exec adfinalproject-service php utils/customersTableVerify.util.php
docker exec adfinalproject-service php utils/productsTableVerify.util.php
```

## 📊 Sample Data Statistics

### Users Database
- **Total Records**: 2
- **Roles**: designer, developer
- **Features**: Secure password hashing, role-based access

### Customers Database  
- **Total Records**: 8
- **Types**: 4 corporate, 4 individual
- **Geographic**: Multiple cyber-cities
- **Financial Range**: $3,299.99 - $45,200.00 total spending

### Products Database
- **Total Records**: 9
- **Categories**: 7 categories (Energy Systems, Neural Upgrades, etc.)
- **Price Range**: $149.99 - $24,999.99
- **Theme**: Cyberpunk enhancement products

## 🎯 Use Cases

### Development Workflow
- **Migration**: Fresh schema creation during development
- **Reset**: Clear data while preserving structure
- **Seeding**: Populate with test data for development
- **Verification**: Validate database state and content

### Testing Scenarios
- **Individual Testing**: Test single database functionality
- **Integration Testing**: Set up specific database combinations
- **Data Validation**: Verify data integrity and relationships

### Production Preparation
- **Schema Updates**: Apply database schema changes
- **Data Migration**: Move from development to production data
- **Health Checks**: Verify database status and content

## 📁 File Organization

```
utils/
├── usersTableMigrate.util.php      # Users migration
├── usersTableReset.util.php        # Users reset
├── usersTableSeeder.util.php       # Users seeding
├── usersTableVerify.util.php       # Users verification
├── customersTableMigrate.util.php  # Customers migration
├── customersTableReset.util.php    # Customers reset
├── customersTableSeeder.util.php   # Customers seeding
├── customersTableVerify.util.php   # Customers verification
├── productsTableMigrate.util.php   # Products migration
├── productsTableReset.util.php     # Products reset
├── productsTableSeeder.util.php    # Products seeding
└── productsTableVerify.util.php    # Products verification
```

## 🔮 Benefits of Separate Utilities

1. **Granular Control**: Manage each database independently
2. **Faster Development**: Work on single databases without affecting others
3. **Easier Debugging**: Isolate issues to specific databases
4. **Flexible Testing**: Set up specific test scenarios
5. **Modular Architecture**: Easy to extend or modify individual components
