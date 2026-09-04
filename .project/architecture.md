# IDRC WCAG Reporter - System Architecture

## High-Level Overview

The IDRC WCAG Reporter is a **report generation system** that processes accessibility audit findings documented in markdown files and produces professional HTML and PDF reports. It follows a three-stage pipeline architecture:

```
Input Documents → Processing Engine → Report Output
(Markdown files)  (Parsing, Validation, (HTML + PDF)
                   Transformation)
```

## Core System Components

### 1. **Content Management Layer**
**Responsibility**: Store and organize audit findings and metadata

- **Audit Report Document** - The main report definition containing:
  - Audit metadata (title, evaluators, date, scope, target WCAG level)
  - Scope definitions (what was and wasn't evaluated)
  - Testing tools and environment details
  - List of technologies tested
  
- **Issue Collection** - Individual markdown files, each representing one accessibility finding:
  - Problem description
  - Affected WCAG Success Criteria
  - Severity classification
  - Difficulty to fix
  - Possible solutions
  - References and links

- **Reference Data** - Static data providing context:
  - WCAG Success Criteria definitions (number, name, level)
  - Severity level definitions
  - Difficulty classifications
  - Multi-language text mappings

### 2. **Data Processing Engine**
**Responsibility**: Transform and organize raw content for rendering

**Key Functions:**
- **Markdown Parser** - Extracts frontmatter (metadata) and content from markdown files
- **Content Validator** - Ensures required fields are present and valid:
  - Required metadata fields (title, evaluators, date, target level)
  - Valid WCAG SC references (e.g., "1.1.1")
  - Valid severity/difficulty values
  
- **Issue Aggregator** - Collects and organizes all issues:
  - Groups issues by WCAG guideline
  - Sorts by SC number
  - Associates severity levels
  
- **Report Builder** - Generates intermediate report structure:
  - Combines audit metadata with processed issues
  - Calculates statistics (total issues, issues by severity)
  - Creates navigation structure for table of contents
  
- **Data Enrichment** - Adds contextual information:
  - Looks up full WCAG SC names and descriptions
  - Maps severity levels to display information
  - Resolves multi-language text

### 3. **Output Generation Layer**
**Responsibility**: Produce final reports in different formats

**HTML Report Generator:**
- Converts processed data to HTML structure
- Applies styling and layout
- Creates navigation and table of contents
- Embeds metadata for accessibility
- Generates index pages and overview sections

**PDF Report Generator:**
- Converts HTML to PDF/UA-1 (universally accessible) format
- Maintains semantic structure for screen readers
- Preserves document outline and navigation
- Applies print-specific styling
- Ensures compliance with PDF accessibility standards

## Data Flow Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    INPUT DOCUMENTS                           │
├─────────────────────────────────────────────────────────────┤
│  Report Index (index.md)                                     │
│  - Metadata: title, evaluators, date, scope, etc.           │
│  - Main report content                                       │
│                                                              │
│  Issue Files (issues/*.md)                                   │
│  - One file per finding                                      │
│  - Metadata: SC references, severity, difficulty            │
│  - Content: problem, solutions, references                  │
│                                                              │
│  Reference Data (JSON/JS)                                    │
│  - WCAG Success Criteria definitions                        │
│  - Severity/difficulty options                              │
├─────────────────────────────────────────────────────────────┤
│              PROCESSING PIPELINE                             │
├─────────────────────────────────────────────────────────────┤
│  1. Content Parsing                                          │
│     - Extract frontmatter from each markdown file            │
│     - Parse human-readable content                          │
│                                                              │
│  2. Validation                                               │
│     - Check required fields present                          │
│     - Validate SC references format                         │
│     - Verify severity values                                │
│                                                              │
│  3. Organization                                             │
│     - Group issues by WCAG guideline                        │
│     - Sort by SC number                                      │
│     - Calculate issue statistics                            │
│                                                              │
│  4. Enrichment                                               │
│     - Add SC definitions                                     │
│     - Resolve language-specific text                        │
│     - Prepare navigation structure                          │
├─────────────────────────────────────────────────────────────┤
│              OUTPUT GENERATION                               │
├─────────────────────────────────────────────────────────────┤
│  HTML Report:                                                │
│  - Structured HTML with semantic markup                     │
│  - Navigation and table of contents                         │
│  - Styled for readability                                    │
│  - Embedded metadata (title, author, etc.)                  │
│                                                              │
│  PDF Report:                                                 │
│  - Generated from HTML output                               │
│  - PDF/UA-1 compliant (accessible PDF standard)            │
│  - Optimized for printing and distribution                 │
└─────────────────────────────────────────────────────────────┘
```

## Key Workflows

### Workflow 1: Creating an Audit Report

1. **Auditor creates report index** with metadata (title, evaluators, dates, scope)
2. **Auditor creates individual issue files** for each finding discovered
3. **System validates** all inputs are correct
4. **System processes** and organizes all findings
5. **System generates** HTML and PDF reports
6. **Auditor reviews** and distributes reports

### Workflow 2: Report Generation

1. **System discovers** all markdown files in designated directories
2. **System reads and parses** each file's frontmatter and content
3. **System validates** data integrity
4. **System queries** reference data (WCAG definitions, localization)
5. **System calculates** statistics and summaries
6. **System renders** reports in target formats
7. **System outputs** final HTML and PDF files

### Workflow 3: Report Navigation and Viewing

1. **User receives** HTML or PDF report
2. **User navigates** using table of contents
3. **User reviews** audit summary and scope
4. **User examines** issues organized by WCAG guideline
5. **User understands** severity and remediation difficulty
6. **User accesses** linked resources and references

## Multi-Language Support

The system supports multiple languages through:
- Language-specific reference data files (WCAG SC definitions, labels)
- Template-based rendering that uses language-appropriate text
- Automatic selection based on configuration
- Currently supported: English, Swedish, and extensible for other languages

## Key Design Principles

1. **Separation of Concerns** - Clear division between content, processing, and output
2. **Markdown as Canonical Format** - Simple, versionable, maintainable format for audit data
3. **Reference-Based Organization** - WCAG Success Criteria as the organizing principle
4. **Metadata-Driven** - Structured metadata for filtering, sorting, and classification
5. **Technology-Agnostic** - Core logic independent of specific rendering technology
6. **Accessibility First** - Both the reports and the system itself are accessible
