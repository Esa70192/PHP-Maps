# PHP-Maps Project Architecture Documentation

## Project Overview

**PHP-Maps** is a custom PHP web application for geospatial data management and visualization. It allows users to upload CSV data files, store them as database tables, filter the data, and visualize geographic information on interactive maps using the TomTom Maps API.

**Technology Stack:**
- Backend: Custom PHP (no framework - procedural/MVC-lite approach)
- Database: MySQL/PDO
- Frontend: HTML5, CSS3, JavaScript
- Mapping Library: TomTom Maps API (v6.20.0)
- Server: Apache (WAMP-compatible)

**Current Status:** Active development with multiple variations in different folders

---

## Directory Structure

```
PHP-Maps/
├── index.php                 # WAMP server homepage (admin/configuration panel)
├── add_vhost.php            # Virtual host configuration utility
├── testmysql.php            # MySQL connection test
├── test_sockets.php         # Socket connectivity test
│
├── prueba/                  # Main application directory (Spanish for "test/proof")
│   ├── BD CAPAS.sql         # Database schema and initialization
│   ├── bdtabla.php          # Database table utilities
│   ├── chat.php             # Chat functionality (legacy/experimental)
│   ├── mapa.php             # Standalone map display
│   ├── mapaid.php           # Map with ID-based features
│   │
│   ├── csv/                 # Sample CSV data files
│   │   ├── CAPA CONTRATOS ADJUDICADOS SIN FAIS MAYO 2025.csv
│   │   ├── CAPA EMERGENCIAS MARZO 2025.csv
│   │   ├── CAPA INFRAESTRUCTURA PUBLICA MAYO 2025.csv
│   │   ├── CAPA PODA 2025.csv
│   │   ├── CAPA SANITARIO ECOLOGICO MARZO 2025.csv
│   │   └── sanitario prueba.csv
│   │
│   ├── global/              # Version 1: Global/Public access variant
│   │   ├── conexiondb.php   # Database connection (PDO)
│   │   ├── login.php        # Login processing
│   │   ├── logout.php       # Session termination
│   │   ├── pag_login.php    # Login page template
│   │   ├── pag_principal.php # Main dashboard
│   │   ├── pag_publica.php  # Public page
│   │   ├── filtro.php       # Dynamic filtering logic
│   │   ├── tablasdb.php     # Table data retrieval
│   │   ├── mapa.php         # Map coordinate extraction
│   │   ├── subir.php        # CSV file upload & processing
│   │   ├── estilo.css       # Styling
│   │   ├── mapa.js          # TomTom map initialization
│   │   ├── mapa1.js         # Alternative map implementation
│   │   └── subir.js         # File upload UI handler
│   │
│   ├── login/               # Version 2: With authentication & signup
│   │   ├── conexiondb.php   # Database connection (same as global)
│   │   ├── login.php        # Login processing
│   │   ├── logout.php       # Logout handler
│   │   ├── signup.php       # User registration
│   │   ├── pag_login.php    # Login page (with recovery link)
│   │   ├── pag_signup.php   # Registration form
│   │   ├── pag_recuperar.php # Password recovery
│   │   ├── recuperar.php    # Password reset logic
│   │   ├── pag_principal.php # Dashboard (requires auth)
│   │   ├── pag_publica.php  # Public accessible page
│   │   ├── mapa.php         # Map handler
│   │   ├── tablasdb.php     # Table management
│   │   ├── subir.php        # File upload
│   │   ├── estilo.css       # Styling
│   │   ├── mapa.js          # Map implementation
│   │   └── subir.js         # Upload UI
│   │
│   └── tablamapa/           # Version 3: Alternate table/map layout
│       ├── conexiondb.php
│       ├── tablasdb.php
│       ├── principal.php
│       ├── mapa.php
│       ├── estilo.css
│       └── mapa.js
│
├── wampthemes/              # WAMP server UI themes
│   ├── modern/
│   ├── classic/
│   ├── simple/
│   ├── no-style/
│   └── *.php                # Modal and theme selection utilities
│
├── wamplangues/             # WAMP multilingual support (12+ languages)
│   ├── index_*.php          # Localized homepage versions
│   ├── add_vhost_*.php      # Localized vhost configuration
│   └── help_*.php           # Localized help content
│
└── .git/                    # Git version control

```

---

## Core Application Flow

### Authentication Flow (Login Variant)

```
pag_login.php (public)
    ↓
[User submits email/password]
    ↓
login.php (POST handler)
    ↓ [Validates credentials]
    ├→ Success: Store session, redirect to pag_principal.php
    └→ Failure: Redirect back with error message
    
pag_signup.php (public registration)
    ↓
signup.php (POST handler)
    ↓ [Hash password, insert user]
    └→ Auto-login and redirect
```

### Main Application Flow (After Authentication)

```
pag_principal.php (Dashboard)
    ├→ [Session check] ← Protected
    ├→ [Load conexiondb.php] ← Database connection
    ├→ [Load filtro.php] ← Build filter UI
    │   └→ [Query SHOW TABLES from 'capas' DB]
    │   └→ [Exclude 'usuarios' table]
    ├→ [Render table selection dropdown]
    ├→ [Render filter controls] (COLONIA, CALLE, NUMERO)
    └→ [Include mapa.php] ← Map coordinates extraction
```

### Data Processing Flow

```
CSV Upload (subir.php)
    ↓
[Parse CSV headers]
    ↓
[Sanitize column names: uppercase, remove special chars, limit 64 chars]
    ↓
[Detect ID_ field and optional columns: CALLE, NUMERO, COLONIA, DESCRIPCION, ACTIVIDAD, TIPO_ACTI, ESPECIE, LATITUD, LONGITUD]
    ↓
[CREATE TABLE with all columns as TEXT]
    ↓
[INSERT all CSV rows with parameterized queries]
    ↓
[Redirect with success message]
```

### Filtering & Map Display Flow

```
[User selects table + applies filters]
    ↓ (POST to pag_principal.php)
    ├→ filtro.php
    │  ├→ [Get unique values for each column]
    │  └→ [Build WHERE clause from filter selections]
    │
    ├→ tablasdb.php
    │  └→ [Execute SELECT with WHERE clause]
    │  └→ [Return filtered data rows]
    │
    └→ mapa.php
       └→ [Extract LATITUD, LONGITUD, ID_ fields]
       └→ [Pass to JavaScript as JSON]
            ↓
            mapa.js
            └→ [Initialize TomTom map]
            └→ [Add markers for each point]
            └→ [Center on first marker]
```

---

## Database Structure

### Database Name: `capas`

#### Table: USUARIOS
```sql
ID_USUARIO (INT, AUTO_INCREMENT, PK)
NOMBRES (VARCHAR 50)
AP_PATERNO (VARCHAR 50)
AP_MATERNO (VARCHAR 50)
CORREO (VARCHAR 255, UNIQUE)
PASSWORD (VARCHAR 255, hashed with PASSWORD_DEFAULT)
```

#### Predefined Data Tables

**EMERGENCIA**
- ID_EMERGENCIA (PK)
- Event details: FENOMENO, TAXONOMIA, RPVE, INTENSIDAD, DESCRIPCION, DETALLES, NOMBRE, FUENTE
- Location: CALLE, NUMERO, COLONIA, CVE_MUN, ALCALDIA, ENTIDAD
- Coordinates: LATITUD, LONGITUD

**SANITARIO_ECOLOGICO**
- Similar structure to EMERGENCIA (ecological/sanitary services)

**INFRAESTRUCTURA**
- ID_INFRA (PK)
- Equipment: EQUI_URBANO, TIPO_ACTI, ACTIVIDAD, FOLIO_CESAC
- Location: CALLE, NUMERO, COLONIA
- Coordinates: LATITUD, LONGITUD
- Metrics: BANQUETA_M2, GUARDICION_ML, DEMOLICION_M2, CIMBRADO_ML, etc.
- Construction details and measurements

**CONTRATOS**
- ID_CONTRATO (PK)
- Contract info: DESCRIPCION, ACTIVIDAD, CONTRATO, RECURSOS_PROYECTO
- Location: CALLE, NUMERO, ENTRE_CALLES, COLONIA
- Coordinates: LATITUD, LONGITUD
- Dates: FECHA_INICIO, FECHA_TERMINO
- Progress: AVANCE
- Extensive project metrics (carpets, bollards, painting, vegetation, etc.)

**PODA** (Tree Pruning)
- ID_PODA (PK)
- Date: FECHA_ATENCION
- Location: COLONIA, CALLE, NUMERO
- Tree: FOLIO, ESPECIE (species)
- Operations: ACLAREO, DESCHUPONEO, ELEVACION_DE_COPA, LIB_CAMARAS, etc. (counts of operations)
- Coordinates: LATITUD, LONGITUD

**ESTADO**
- ID_ESTADO (PK)
- DESCRIPCION (status descriptions)

#### Dynamic Tables
- User-uploaded CSV files are created as new tables at runtime
- All columns stored as TEXT type
- Column names normalized: uppercase, special chars → underscore, max 64 chars

---

## Configuration & Credentials

### Database Connection
**File:** `prueba/[global|login|tablamapa]/conexiondb.php`

```php
$host = "localhost"
$dbname = "capas"
$user = "root"
$pass = ""  // Empty default, should be configured
```

**Connection Method:** PDO with error handling

### Third-Party APIs
- **TomTom Maps API Key:** Embedded in `prueba/*/mapa.js`
  - Key: `dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y`
  - Maps SDK v6.20.0
  - Default center: Mexico City (-99.19, 19.425)

### Session Management
- PHP `session_start()` used throughout
- Session variables:
  - `$_SESSION['usuario_id']`
  - `$_SESSION['nombre']`
  - `$_SESSION['login_error']`
  - `$_SESSION['error_nombre']`

---

## Key Features & Functionality

### 1. User Authentication (Login/Signup variant)
- Email + password registration
- Password hashing: `PASSWORD_DEFAULT` (bcrypt)
- Email validation: `FILTER_VALIDATE_EMAIL`
- Duplicate email checking
- Auto-login after registration
- Password recovery flow (partially implemented)

### 2. CSV Data Management
- Upload CSV files with automatic table creation
- Dynamic schema generation from headers
- Duplicate column name handling
- BOM (Byte Order Mark) stripping from UTF-8
- Parameterized SQL inserts for security

### 3. Data Filtering
- Dynamic filter UI based on table columns
- Fixed filter columns: COLONIA, CALLE, NUMERO
- Optional display columns: DESCRIPCION, ACTIVIDAD, TIPO_ACTI, ESPECIE
- Filter values extracted as DISTINCT queries
- Multi-column AND filtering

### 4. Geospatial Visualization
- TomTom interactive map display
- Automatic latitude/longitude detection
- Custom markers with ID labels
- Dynamic zoom/pan to data
- Map initialization parameters in JavaScript

### 5. Table Display
- Dynamic table rendering
- Column filtering (ID_ fields prioritized)
- Optional columns for context
- Data extraction from filtered queries

---

## Security Considerations

### Implemented
- ✓ Parameterized queries (PDO prepared statements)
- ✓ Password hashing (PASSWORD_DEFAULT)
- ✓ Session-based authentication
- ✓ SQL injection prevention in uploads (column name validation)

### Recommendations for Future
- [ ] HTTPS enforcement
- [ ] CSRF tokens on forms
- [ ] Rate limiting on login attempts
- [ ] Input sanitization on all user inputs
- [ ] SQL error message suppression in production
- [ ] Remove hardcoded TomTom API key (use environment variable)
- [ ] Database user password configuration
- [ ] Implement escape on dynamic table/column names in DESCRIBE queries

---

## Testing & Utilities

### Test Files
- **testmysql.php** - MySQL connection verification
  - Tests: Connection, host info, server version, charset
  - Useful for environment debugging
  
- **test_sockets.php** - Socket connectivity (not analyzed)

### Sample Data
- 6 CSV files in `prueba/csv/` folder
- All contain geographic/municipal data with LATITUD/LONGITUD
- Used for testing upload and filtering functionality

---

## Application Variants

The codebase contains **3 parallel implementations** with varying features:

| Feature | global/ | login/ | tablamapa/ |
|---------|---------|--------|-----------|
| Authentication Required | No | Yes | Unknown |
| User Registration | No | Yes | Unknown |
| File Upload | Yes | Yes | Unknown |
| Map Display | Yes | Yes | Yes |
| Public Access Page | Yes (pag_publica.php) | Yes (pag_publica.php) | Unknown |
| Password Recovery | No | Yes | Unknown |

**Recommendation:** Consolidate into single version with feature flags or clean up unused variants.

---

## WAMP Server Integration

This project includes WAMP-specific utilities (appears to be part of a larger WAMP installation):

- **index.php** - WAMP server homepage
  - Lists projects, aliases, virtual hosts
  - Shows MySQL/PHP/Apache versions
  - Multilingual interface (12+ languages)
  - Theme selector (modern, classic, simple, no-style)

- **add_vhost.php** - Virtual host management
  - Requires specific WAMP configuration files
  - Includes localized help and guides

**Note:** These are separate from the PHP-Maps application itself and support the WAMP server environment.

---

## Multilingual Support

The WAMP components support **12+ languages:**
- English, Spanish, French, Portuguese, Romanian
- Czech, Chinese, Bulgarian, Turkish, Macedonian, Latvian, Greek (Hellenic)

Implemented via:
- Language-specific PHP files: `wamplangues/index_*.php`, `add_vhost_*.php`, `help_*.php`
- GET parameter `?lang=xx` for language switching
- Fallback to English + language-specific overrides

---

## Development Notes

### Entry Points
1. **WAMP Admin:** `/index.php` (project listings)
2. **Application - Public (global):** `/prueba/global/pag_login.php`
3. **Application - Authenticated (login):** `/prueba/login/pag_login.php` → `/prueba/login/pag_principal.php`
4. **Direct Map:** `/prueba/mapa.php`

### Debugging Resources
- Database: `testmysql.php`
- Network: `test_sockets.php`
- Data: Sample CSVs in `prueba/csv/`

### Git Commits
Recent activity (from commit log):
- `dbaa641` - sql
- `e09cb2c` - si
- `f68ae2e` - Confnuevapc
- `95639d2` - Confnuevapc
- `24e382a` - Confnuevapc

**Note:** Commit messages are minimal; consider adopting conventional commits.

---

## Quick Reference Commands

### Start Development
```bash
# Ensure WAMP/MySQL is running
# Navigate to project root
cd /home/david/Proyectos/PHP/PHP-Maps

# Access application
# Option 1: Global (no auth)
http://localhost/prueba/global/pag_login.php

# Option 2: Login-based (with auth)
http://localhost/prueba/login/pag_login.php

# Test database connection
http://localhost/testmysql.php
```

### Database Setup
```sql
-- Import schema
mysql -u root < prueba/BD\ CAPAS.sql

-- Verify
mysql -u root
> USE capas;
> SHOW TABLES;
> SELECT * FROM usuarios;
```

### File Upload Test
- Use CSV files from `prueba/csv/`
- Ensure columns include LATITUD, LONGITUD for map display
- Column naming: Will be normalized to UPPERCASE with underscores

---

## Dependencies & Requirements

**Server:**
- Apache (WAMP compatible)
- PHP 7.4+ (uses modern syntax: arrow functions, match)
- MySQL 5.7+ / MariaDB 10+

**PHP Extensions:**
- `pdo`
- `pdo_mysql`
- `fileinfo` (for file uploads)

**Frontend Libraries (CDN):**
- TomTom Maps SDK: https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/

**Credentials Needed:**
- TomTom API Key (already embedded: `dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y`)
- MySQL root user (default password empty)

---

## Known Issues & TODOs

1. **Hardcoded API Key** - TomTom key visible in `mapa.js`
2. **Multiple Variants** - Three parallel implementations (global/login/tablamapa)
3. **Minimal Testing** - Test files exist but no automated tests
4. **Sparse Documentation** - Only SQL file and inline comments
5. **Password Recovery** - Flow partially implemented in login variant
6. **Error Handling** - Some error displays to users (should be logged instead)
7. **Session Security** - No timeout or additional security headers
8. **Database Credentials** - Default root/empty password configuration

---

## For Next Developer

1. **Decide on single variant** - Consolidate global/login/tablamapa or document purpose of each
2. **Move credentials to .env** - TomTom API key, DB password, configuration
3. **Add .htaccess rules** - If Apache mod_rewrite needed for cleaner URLs
4. **Implement error logging** - Instead of displaying errors to users
5. **Add test suite** - Unit tests for upload, filtering, authentication
6. **Document API** - If exposing data via AJAX endpoints
7. **Review security** - Implement CSRF tokens, rate limiting, HTTPS
8. **Upgrade commit messages** - Use semantic commit format

---

**Last Updated:** 2025-10-21
**Project Type:** Geospatial Data Management & Visualization System
**Status:** Active Development
