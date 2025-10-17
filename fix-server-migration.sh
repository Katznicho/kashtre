#!/bin/bash

# Fix server migration issue by removing old problematic migration file
echo "Removing old problematic migration file..."

# Remove the old migration file that has the $this->command issue
rm -f database/migrations/2025_10_17_221133_cleanup_duplicate_branch_item_prices.php

echo "Old migration file removed successfully!"
echo "The correct migration 2025_10_17_200000_cleanup_duplicate_branch_item_prices.php should remain."
