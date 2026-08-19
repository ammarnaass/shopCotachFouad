#!/bin/bash
# Deploy Script for ShopCotachFouad
# Usage: ./scripts/deploy.sh [dev|prod]

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
PROJECT_NAME="shopcotachfouad"
DEV_COMPOSE="docker-compose.yml"
PROD_COMPOSE="docker-compose.prod.yml"

# Get environment
ENV=${1:-dev}

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  ShopCotachFouad - Deploy Script${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}✗ Docker is not running. Please start Docker.${NC}"
    exit 1
fi

# Function to deploy
deploy() {
    local COMPOSE_FILE=$1
    local ENV=$2

    echo -e "${YELLOW}Deploying in ${ENV} mode...${NC}"
    echo ""

    # Stop existing containers
    echo -e "${YELLOW}[1/6] Stopping existing containers...${NC}"
    docker-compose -f $COMPOSE_FILE down

    # Build and start containers
    echo -e "${YELLOW}[2/6] Building and starting containers...${NC}"
    docker-compose -f $COMPOSE_FILE up -d --build

    # Wait for MySQL
    echo -e "${YELLOW}[3/6] Waiting for MySQL to be ready...${NC}"
    max_attempts=30
    attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if docker exec shopcotachfouad-mysql mysqladmin ping -h localhost -u root -proot_pass_2026 > /dev/null 2>&1; then
            echo -e "${GREEN}  ✓ MySQL is ready!${NC}"
            break
        fi
        attempt=$((attempt + 1))
        echo -e "  Attempt $attempt/$max_attempts - waiting..."
        sleep 2
    done

    if [ $attempt -eq $max_attempts ]; then
        echo -e "${RED}  ✗ Failed to connect to MySQL${NC}"
        exit 1
    fi

    # Run migrations
    echo -e "${YELLOW}[4/6] Running migrations...${NC}"
    docker exec shopcotachfouad-app php artisan migrate --force

    # Run seeders (only if users table is empty)
    user_count=$(docker exec shopcotachfouad-app php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo "0")
    if [ "$user_count" = "0" ]; then
        echo -e "${YELLOW}[5/6] Running seeders (database is empty)...${NC}"
        docker exec shopcotachfouad-app php artisan db:seed --force
    else
        echo -e "${YELLOW}[5/6] Skipping seeders (database has $user_count users)${NC}"
    fi

    # Final setup
    echo -e "${YELLOW}[6/6] Final setup...${NC}"
    docker exec shopcotachfouad-app php artisan config:clear
    docker exec shopcotachfouad-app php artisan view:clear
    docker exec shopcotachfouad-app php artisan cache:clear
    docker exec shopcotachfouad-app php artisan storage:link --force 2>/dev/null || true

    echo ""
    echo -e "${GREEN}=========================================${NC}"
    echo -e "${GREEN}  ✓ Deployment complete!${NC}"
    echo -e "${GREEN}  App: http://localhost${NC}"
    echo -e "${GREEN}  Environment: ${ENV}${NC}"
    echo -e "${GREEN}=========================================${NC}"
}

# Deploy based on environment
if [ "$ENV" = "prod" ]; then
    deploy $PROD_COMPOSE "production"
else
    deploy $DEV_COMPOSE "development"
fi
