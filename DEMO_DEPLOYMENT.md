# Demo Environment Deployment Guide

This guide explains how to deploy the Kashtre application to the demo environment at `https://demo.kashtre.com`.

## 🚀 Quick Deployment

### Automatic Deployment (GitHub Actions)
The demo environment is automatically deployed when you push to the `demo` branch:

```bash
git checkout demo
git push origin demo
```

### Manual Deployment
Use the deployment script:

```bash
./deploy-demo.sh
```

## 📋 Prerequisites

### GitHub Secrets
Configure these secrets in your GitHub repository settings:

- `DEMO_HOST` - Demo server hostname/IP
- `DEMO_USERNAME` - SSH username for demo server
- `DEMO_SSH_KEY` - Private SSH key for demo server
- `DEMO_PORT` - SSH port (usually 22)

### Server Requirements
- PHP 8.2+
- Composer
- MySQL 8.0+
- Git
- SSH access

## 🔧 Setup Instructions

### 1. Create Demo Branch
```bash
git checkout -b demo
git push -u origin demo
```

### 2. Configure Environment
Copy the demo environment file:
```bash
cp demo.env.example .env
```

Update the database credentials and other settings as needed.

### 3. Deploy
```bash
./deploy-demo.sh
```

## 📁 File Structure

```
.github/workflows/
├── deploy-demo.yml          # GitHub Actions workflow
├── deploy-demo.sh           # Manual deployment script
├── demo.env.example         # Demo environment template
└── DEMO_DEPLOYMENT.md       # This file
```

## 🔄 Deployment Process

1. **Code Checkout** - Pulls latest code from demo branch
2. **Dependencies** - Installs Composer packages
3. **Optimization** - Caches config, routes, and views
4. **Database** - Runs migrations and seeds
5. **Permissions** - Sets proper file permissions
6. **Cache Clear** - Clears all caches

## 🧪 Testing

After deployment, test these key features:

- [ ] Login functionality
- [ ] Sub-groups page: `/sub-groups`
- [ ] Dashboard
- [ ] All CRUD operations
- [ ] File uploads
- [ ] Email notifications

## 🐛 Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R u242329769:u242329769 storage bootstrap/cache
   ```

2. **Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Database Issues**
   ```bash
   php artisan migrate:fresh --seed
   ```

### Logs
Check application logs:
```bash
tail -f storage/logs/laravel.log
```

## 🔐 Security Notes

- Demo environment uses `APP_DEBUG=true` for development
- Database credentials should be different from production
- Consider using demo-specific API keys
- Regular security updates recommended

## 📞 Support

For deployment issues:
1. Check GitHub Actions logs
2. Review server logs
3. Verify environment configuration
4. Test database connectivity

---

**Demo URL**: https://demo.kashtre.com  
**Branch**: `demo`  
**Last Updated**: $(date)
