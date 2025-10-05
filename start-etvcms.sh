#!/bin/bash

# Credence e-TVCMS Startup Script
# This script helps you get started with the e-TVCMS system

echo "==========================================="
echo "Credence e-TVCMS - TV Content Procurement Management System"
echo "==========================================="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

echo "✅ Docker is running"

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose is not installed. Please install docker-compose first."
    exit 1
fi

echo "✅ docker-compose is available"

# Start the containers
echo ""
echo "🚀 Starting e-TVCMS containers..."
docker-compose up -d

# Wait for containers to be ready
echo ""
echo "⏳ Waiting for containers to be ready..."
sleep 10

# Check if containers are running
if docker-compose ps | grep -q "Up"; then
    echo "✅ Containers are running successfully!"
    echo ""
    echo "🌐 Access your e-TVCMS system:"
    echo "   Main Website: http://localhost:8080"
    echo "   Database Admin: http://localhost:8081"
    echo ""
    echo "📋 Next Steps:"
    echo "   1. Visit http://localhost:8080 to install Drupal"
    echo "   2. Use these database settings during installation:"
    echo "      - Database name: tender_management"
    echo "      - Username: drupal"
    echo "      - Password: drupal123"
    echo "      - Host: db"
    echo "      - Port: 5432"
    echo "   3. After installation, enable custom modules:"
    echo "      - Go to /admin/modules"
    echo "      - Enable 'User Management' and 'Content Management'"
    echo "   4. Visit /content/dashboard for the main system"
    echo ""
    echo "🎯 e-TVCMS Features Ready:"
    echo "   ✅ Content Procurement Management"
    echo "   ✅ Producer Registration & Certification"
    echo "   ✅ Proposal Submission & Evaluation"
    echo "   ✅ Production Contract Management"
    echo "   ✅ Document Management System"
    echo "   ✅ Workflow Management"
    echo "   ✅ Reporting & Analytics"
    echo ""
    echo "📚 Content Types Supported:"
    echo "   • Swasta Baharu (Local New Private)"
    echo "   • Sambung Siri (Series Continuation)"
    echo "   • Program Luar Negara (International)"
    echo "   • Produk Siap Tempatan (Local Finished)"
    echo ""
else
    echo "❌ Some containers failed to start. Check with:"
    echo "   docker-compose ps"
    echo "   docker-compose logs"
fi

echo "==========================================="