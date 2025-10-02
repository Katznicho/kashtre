#!/bin/bash

# Demo Environment Deployment Script
# Usage: ./deploy-demo.sh

set -e

echo "🚀 Starting deployment to demo.kashtre.com..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DEMO_HOST="your-demo-server.com"
DEMO_USER="your-username"
DEMO_PATH="/home/u242329769/domains/demo.kashtre.com/public_html"
BRANCH="demo"

echo -e "${BLUE}📋 Deployment Configuration:${NC}"
echo "  Host: $DEMO_HOST"
echo "  User: $DEMO_USER"
echo "  Path: $DEMO_PATH"
echo "  Branch: $BRANCH"
echo ""

# Check if we're on the demo branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
    echo -e "${YELLOW}⚠️  Warning: You're not on the demo branch (current: $CURRENT_BRANCH)${NC}"
    read -p "Do you want to switch to demo branch? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git checkout demo
        git pull origin demo
    else
        echo -e "${RED}❌ Deployment cancelled${NC}"
        exit 1
    fi
fi

# Pull latest changes
echo -e "${BLUE}📥 Pulling latest changes...${NC}"
git pull origin demo

# Run tests locally
echo -e "${BLUE}🧪 Running tests...${NC}"
php artisan test

# Deploy to server
echo -e "${BLUE}🚀 Deploying to demo server...${NC}"
ssh $DEMO_USER@$DEMO_HOST << EOF
    set -e
    echo "📁 Navigating to demo directory..."
    cd $DEMO_PATH
    
    echo "📥 Fetching latest changes..."
    git fetch origin
    git reset --hard origin/$BRANCH
    
    echo "📦 Installing dependencies..."
    composer install --no-dev --optimize-autoloader
    
    echo "⚙️  Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "🗄️  Running migrations..."
    php artisan migrate --force
    
    echo "🌱 Seeding database..."
    php artisan db:seed --force
    
    echo "🔧 Setting permissions..."
    chmod -R 755 storage bootstrap/cache
    chown -R u242329769:u242329769 storage bootstrap/cache
    
    echo "🧹 Clearing caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    echo "✅ Demo deployment completed!"
EOF

echo ""
echo -e "${GREEN}🎉 Deployment to demo.kashtre.com completed successfully!${NC}"
echo -e "${BLUE}🌐 Demo site: https://demo.kashtre.com${NC}"
echo ""
echo -e "${YELLOW}📝 Next steps:${NC}"
echo "  1. Test the demo site functionality"
echo "  2. Check sub-groups page: https://demo.kashtre.com/sub-groups"
echo "  3. Verify all features are working correctly"
echo ""
