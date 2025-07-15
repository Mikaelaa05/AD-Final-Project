# Customer Database System

## 📋 Overview
Complete customer database system with cyberpunk-themed data for the AD Final Project.

## 🏗️ Database Schema
- **Table Name**: `customers`
- **Primary Key**: `id` (UUID)
- **Unique Fields**: `customer_code`, `email`
- **20 Fields Total**: Including contact info, address, financial data, and activity status

### Key Fields:
- `customer_code`: Unique identifier (CUST001, CUST002, etc.)
- `company_name`: For corporate customers (nullable)
- `first_name`, `last_name`: Customer names
- `email`, `phone`: Contact information
- `address_line1/2`, `city`, `state`, `postal_code`, `country`: Full address
- `customer_type`: 'individual' or 'corporate'
- `credit_limit`, `total_orders`, `total_spent`: Financial tracking
- `is_active`: Account status
- `notes`: Additional information
- `created_at`, `updated_at`: Timestamps

## 📊 Sample Data
8 cyberpunk-themed customers including:
- **Corporate Clients**: SINthesize Corp, CyberTech Industries, Neon Dynamics LLC, Binary Solutions Inc
- **Individual Customers**: Marcus Chen, Jake Morrison, Aria Blackwood, Zara Nova, Maya Storm
- **Geographic Spread**: Neo Tokyo, Chrome City, Night City, Data Haven, Cyber Angeles, etc.
- **Financial Range**: $3,299.99 to $45,200.00 in total spending

## 🔧 Utilities

### Migration
```bash
docker exec adfinalproject-service php utils/customersTableMigrate.util.php
```
- Drops existing table
- Creates fresh schema
- Sets up all constraints

### Reset
```bash
docker exec adfinalproject-service php utils/customersTableReset.util.php
```
- Ensures table exists
- Truncates all data
- Resets identity counters

### Seeding
```bash
docker exec adfinalproject-service php utils/customersTableSeeder.util.php
```
- Creates table if needed
- Clears existing data
- Inserts 8 sample customers
- Shows summary statistics

### Verification
```bash
docker exec adfinalproject-service php utils/customersTableVerify.util.php
```
- Confirms table existence
- Shows customer counts
- Displays type breakdown
- Lists top customers by spending
- Shows sample records

## 🚀 Quick Start
1. **Create**: `docker exec adfinalproject-service php utils/customersTableMigrate.util.php`
2. **Populate**: `docker exec adfinalproject-service php utils/customersTableSeeder.util.php`
3. **Verify**: `docker exec adfinalproject-service php utils/customersTableVerify.util.php`

## 📈 Statistics
- **Total Customers**: 8
- **Corporate**: 4 customers
- **Individual**: 4 customers  
- **Active**: 7 customers
- **Inactive**: 1 customer (Maya Storm - payment issues resolved)
- **Total Revenue**: $129,750.99

## 🎯 Business Features
- Credit limit tracking
- Order history
- Customer segmentation (individual/corporate)
- Geographic distribution
- Activity status management
- Detailed notes for customer service

## 🔮 Cyberpunk Theme
All data follows the project's cyberpunk aesthetic with:
- Futuristic company names (SINthesize Corp, CyberTech Industries)
- Cyber-themed addresses (Neon Boulevard, Digital Street, Underground Avenue)
- Tech-savvy email domains (.corp, .ind, .net, .grid)
- Dystopian city names (Neo Tokyo, Night City, Data Haven)
