# Deployment Guide

## Option 1: Free PHP Hosting (Recommended)

Since this portfolio uses PHP and MySQL, use these free hosting options:

### InfinityFree (Free)
1. Sign up at https://infinityfree.net
2. Create a new account/hosting
3. Upload files via FTP (FileZilla)
4. Create MySQL database in control panel
5. Your site will be live at: `yourname.epizy.com`

### 000webhost (Free)
1. Sign up at https://www.000webhost.com
2. Create new website
3. Upload via File Manager or FTP
4. MySQL included free
5. Live at: `yourname.000webhostapp.com`

---

## Option 2: GitHub Pages (Static Portfolio Only)

GitHub Pages is free but only hosts static files (no PHP).
Your demos won't work, but the main portfolio will display.

### Steps:
```bash
# In your Portfolio folder
git init
git add .
git commit -m "Initial commit - portfolio"
git branch -M main
git remote add origin https://github.com/khan1020/portfolio.git
git push -u origin main
```

Then in GitHub:
1. Go to repository Settings
2. Pages → Source: "main" branch
3. Save → Your site: `khan1020.github.io/portfolio`

---

## Option 3: Paid Shared Hosting ($3-5/month)

Best for full functionality:
- **Hostinger** - $2.99/mo
- **Namecheap** - $3.88/mo  
- **Bluehost** - $4.95/mo

All include:
- PHP 7.4+
- MySQL databases
- Free SSL
- FTP access
- One-click WordPress (optional)

---

## Quick Git Commands

```bash
# Navigate to portfolio
cd C:\xampp\htdocs\Portfolio

# Initialize git
git init

# Add all files
git add .

# Commit
git commit -m "My portfolio - 15 working demo projects"

# Add remote (create repo on GitHub first!)
git remote add origin https://github.com/khan1020/portfolio.git

# Push
git push -u origin main
```

---

## For Fiverr/LinkedIn

Once hosted, add these links to your profiles:

- **Portfolio URL**: `https://your-domain.com` or `https://khan1020.github.io/portfolio`
- **GitHub**: `https://github.com/khan1020/portfolio`

### Fiverr Gig Description Example:
> "Full-stack developer with 15+ live demo projects. Check my portfolio to see working e-commerce, chat apps, booking systems, and more!"

---

Built by Afzal Khan | January 2026
