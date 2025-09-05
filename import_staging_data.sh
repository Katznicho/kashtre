#!/bin/bash

# Script to import staging data without recreating tables
# This script extracts only INSERT statements from the SQL dump

echo "✓ Extracting INSERT statements from staging data..."

# Extract only INSERT statements and remove table creation statements
grep -E "^INSERT INTO|^-- Dumping data for table" database/staging_data.sql > database/inserts_only.sql

# Add proper SQL headers
cat > database/data_import.sql << 'EOF'
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
EOF

# Append the INSERT statements
cat database/inserts_only.sql >> database/data_import.sql

# Add SQL footer
cat >> database/data_import.sql << 'EOF'
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
EOF

echo "✓ Data import file created: database/data_import.sql"
echo "✓ Ready to import data without recreating tables"
