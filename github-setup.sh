#!/bin/bash

###############################################################################
# Buses - GitHub Deployment Guide
# How to deploy Buses MVP to GitHub with CanTicket base
###############################################################################

echo "========================================="
echo "Buses GitHub Setup Guide"
echo "========================================="
echo ""

# STEP 1: Create GitHub Repository
echo "STEP 1: Create GitHub Repository"
echo "--------------------------------"
echo "1. Go to: https://github.com/new"
echo "2. Repository name: buses-mvp"
echo "3. Description: Bus company operations and staff management platform"
echo "4. Visibility: Private (recommended for MVP)"
echo "5. DO NOT initialize with README (we have our own)"
echo "6. Click 'Create repository'"
echo ""
read -p "Press Enter when repository is created..."

# STEP 2: Initialize Local Git Repository
echo ""
echo "STEP 2: Initialize Local Git Repository"
echo "---------------------------------------"
cd /Users/cassandracadorin/Buses

if [ -d ".git" ]; then
    echo "✓ Git repository already initialized"
else
    git init
    echo "✓ Git repository initialized"
fi

# STEP 3: Create .gitignore
echo ""
echo "STEP 3: Creating .gitignore..."
cat > .gitignore << 'EOF'
# Laravel
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.env.production
.phpunit.result.cache
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log

# IDE
/.idea
/.vscode
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Build files
/public/build
/public/mix-manifest.json

# Logs
*.log
/storage/logs/*
!/storage/logs/.gitkeep

# Uploaded files (checklists photos)
/storage/app/public/checklist_photos/*
!/storage/app/public/checklist_photos/.gitkeep

# Testing
/coverage
/.phpunit.cache

# Deployment
deploy-config.php
EOF
echo "✓ .gitignore created"

# STEP 4: Create README with deployment instructions
echo ""
echo "STEP 4: README already exists"
echo "✓ Using existing README.md"

# STEP 5: Stage all files
echo ""
echo "STEP 5: Staging files for commit..."
git add .
echo "✓ Files staged"

# STEP 6: Initial commit
echo ""
echo "STEP 6: Creating initial commit..."
git commit -m "Initial commit: Buses MVP v1.0.0

- End-of-day safety checklists (tyre, fuel, kids check)
- Vehicle fleet management
- Kids left alert system
- Manager review interface
- Checklist reporting & analytics
- Built on CanTicket Laravel 11 foundation (90% reuse)
- Dreamscape hosting ready
- Complete documentation included

Budget: AU\$12K-18K
Timeline: 3-4 weeks
Reuses: Auth, time tracking, leave, scheduling, reporting from CanTicket"

echo "✓ Initial commit created"

# STEP 7: Add remote repository
echo ""
echo "STEP 7: Adding GitHub remote..."
echo "Enter your GitHub repository URL"
echo "Example: https://github.com/CanTicket/buses-mvp.git"
read -p "GitHub URL: " GITHUB_URL

if [ -z "$GITHUB_URL" ]; then
    echo "⚠ No URL provided. Skipping remote setup."
    echo "You can add it later with:"
    echo "git remote add origin YOUR_GITHUB_URL"
else
    git remote add origin "$GITHUB_URL"
    echo "✓ Remote 'origin' added"
fi

# STEP 8: Push to GitHub
echo ""
echo "STEP 8: Pushing to GitHub..."
if [ -z "$GITHUB_URL" ]; then
    echo "⚠ Skipped (no remote URL)"
else
    git branch -M main
    git push -u origin main
    echo "✓ Code pushed to GitHub!"
fi

echo ""
echo "========================================="
echo "GitHub Setup Complete!"
echo "========================================="
echo ""
echo "Your repository is now available at:"
echo "$GITHUB_URL"
echo ""
echo "Next steps:"
echo "1. Add collaborators (Settings → Collaborators)"
echo "2. Enable branch protection for 'main'"
echo "3. Setup GitHub Actions for CI/CD (optional)"
echo "4. Clone CanTicket codebase separately"
echo "5. Merge Buses files into CanTicket structure"
echo ""
echo "To integrate with CanTicket:"
echo "1. Clone CanTicket: git clone https://github.com/CanTicket/canticket-laravel.git"
echo "2. Copy Buses files into CanTicket directory"
echo "3. Run migrations: php artisan migrate"
echo "4. Test locally: php artisan serve"
echo ""



