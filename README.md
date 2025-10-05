# Credence e-TVCMS - TV Content Procurement Management System

A Drupal 10-based TV content procurement management system with PostgreSQL database, aligned with RTM e-TVCMS functionality.

## Features

### Core Modules (Based on e-TVCMS Requirements)

1. **Platform Module**
   - Backend & Frontend Management
   - CMS & Online Web Portal

2. **User Management Module**
   - Email-based user registration and authentication
   - Admin management, user registration, access management
   - Role management, approval management, profile management
   - Producer certification workflow

3. **Content Procurement Management (Tender Management)**
   - Content procurement creation and publishing
   - Content categories: Swasta Baharu, Sambung Siri, Program Luar Negara, Produk Siap Tempatan
   - Vendor registration and management (Content Producers)
   - Notification system for vendor registration
   - Selection process, pitching session evaluation, content evaluation
   - Proposal evaluation and decision-making

4. **Document Management**
   - Content uploading, workflow management
   - Automated templates, document & video management
   - Content syndication support

5. **Contract Management**
   - Production contract listing, contract property management
   - Performance review, payment status tracking
   - Contract view & reporting, penalty management
   - Award assessment & reporting, payment view & reporting

6. **Reporting Module**
   - Usage tracking (historical & trending)
   - View & printed version reports
   - Audit trail and activity logs

7. **REST API Integration**
   - Integration with procurement management system
   - Integration with cloud system
   - SMTP & email notification system

## Content Types Supported

- **Swasta Baharu (Local New Private)** - Terbitan Tempatan
- **Sambung Siri (Series Continuation)** - Terbitan Tempatan  
- **Program Luar Negara (International Programs)** - Syndicated
- **Produk Siap Tempatan (Local Finished Products)** - Finished Product

## System Requirements

- Docker and Docker Compose
- Minimum 4GB RAM
- 10GB free disk space

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd credence-tender-management
```

2. Start the Docker containers:
```bash
docker-compose up -d
```

3. Install Drupal:
- Visit http://localhost:8080
- Follow the installation wizard
- Use PostgreSQL database with these settings:
  - Database name: tender_management
  - Username: drupal
  - Password: drupal123
  - Host: db
  - Port: 5432

4. Enable custom modules:
- Go to /admin/modules
- Enable "User Management" and "Content Management" modules

5. Access the system:
- Main site: http://localhost:8080
- Content Dashboard: http://localhost:8080/content/dashboard
- Producer Registration: http://localhost:8080/producer/register
- Database management: http://localhost:8081 (Adminer)

## User Roles & Permissions

### Content Producers
- Register with company details and production capabilities
- Submit proposals for content procurement
- Manage production contracts
- Upload content deliverables

### RTM Content Managers
- Create and manage content procurement opportunities
- Evaluate and score proposals
- Award contracts to selected producers
- Monitor production progress

### RTM Administrators
- Certify and manage content producers
- Oversee entire procurement process
- Generate reports and analytics
- Manage system configurations

## Custom Modules

### User Management Module (`custom/modules/user_management/`)
- Enhanced user registration with company information
- Email-based authentication system
- User verification and approval workflow
- Extended user profiles for content producers

### Content Management Module (`custom/modules/content_management/`)
- Content procurement creation and management
- Producer registration and certification
- Proposal submission and evaluation system
- Production contract management
- Workflow management for approval processes

## Database Schema

The system includes specialized tables for TV content procurement:
- `content_procurements` - Content procurement opportunities
- `content_producers` - Producer company profiles
- `content_proposals` - Producer submissions/bids
- `production_contracts` - Awarded production contracts
- `evaluation_criteria` - Scoring criteria for proposals
- `evaluation_scores` - Evaluation results
- `content_workflow` - Workflow management
- `procurement_documents` - Document management

## API Endpoints

REST API endpoints available at:
- `/api/content/procurements` - Content procurement operations
- `/api/content/proposals` - Proposal management
- `/api/producers` - Producer management
- `/api/contracts` - Contract operations

## Development

The system structure:
- `custom/modules/` - Custom Drupal modules
- `custom/themes/` - Custom themes for e-TVCMS interface
- `database/init/` - Database initialization scripts
- `config/` - Drupal configuration files

## Features Alignment with e-TVCMS

✅ **Platform**: Backend/Frontend CMS with online web portal
✅ **User Management**: Email registration, role management, approval workflow
✅ **Content Procurement**: RTM-style tender management for TV content
✅ **Document Management**: Content upload, workflow, automated templates
✅ **Contract Management**: Production contracts, performance tracking, payments
✅ **Reporting**: Historical tracking, audit trails, printed reports
✅ **API Integration**: REST APIs for external system integration

## License

Proprietary - Credence Technologies