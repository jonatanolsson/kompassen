# IDRC WCAG Reporter - Implementation Guide

This guide describes what you need to build if you want to create your own version of the IDRC WCAG Reporter. It is technology-agnostic and provides implementation requirements rather than specific code solutions.

## System Requirements (Technology-Agnostic)

Your implementation needs to:
1. **Read and parse markdown files** with frontmatter metadata
2. **Validate input data** against defined rules
3. **Transform and organize data** for report generation
4. **Generate HTML output** with proper semantic structure
5. **Generate PDF/UA-1 output** from the HTML
6. **Support multi-language rendering** with configurable text strings

### Core Dependencies (Language-Agnostic)

Your system requires:
- **Markdown parser** - Library that can extract frontmatter and parse markdown content
- **HTML templating engine** - To convert data to HTML structure
- **CSS styling engine** - To apply styling to HTML
- **PDF generation library** - With PDF/UA-1 accessibility support
- **Configuration management** - For language selection and system settings

These exist in most programming ecosystems. Choose based on your preferred technology stack.

## Core Modules to Build

### Module 1: Input Parser

**Responsibility**: Read and extract data from markdown files

**Inputs**:
- Markdown files from disk (report index + individual issues)
- File paths/directories to search

**Processing**:
- Discover markdown files in designated directories
- Parse frontmatter (YAML metadata section)
- Extract markdown content (body)
- Handle missing or malformed files gracefully

**Outputs**:
- Structured data representing each file:
  - metadata object (parsed YAML)
  - content string (markdown body)
  - metadata about the file (path, name)

**Example Operation**:
```
Input:  src/report/issues/missing-alt.md
Output: {
  metadata: {title: "...", sc: ["1.1.1"], severity: "major"},
  content: "## Problem\n...\n## Solutions\n...",
  filePath: "src/report/issues/missing-alt.md"
}
```

**Implementation Notes**:
- Handle YAML parsing (most languages have libraries)
- Preserve markdown formatting in content
- Gracefully handle missing frontmatter
- Track file locations for error reporting

---

### Module 2: Data Validator

**Responsibility**: Ensure data quality and correctness

**Inputs**:
- Parsed document data (from Input Parser)
- Reference data (valid SC numbers, severity levels)
- Validation rules configuration

**Processing**:
- Check required fields are present
- Validate field formats (e.g., SC must match "X.X.X")
- Verify references exist (e.g., SC "1.1.1" is valid)
- Validate severity and difficulty values are allowed
- Check for data consistency

**Outputs**:
- Validation result (success or error list)
- Detailed error messages for debugging
- Warnings for non-critical issues

**Validation Rules**:

*For Report Document*:
- Required: title, evaluators, commissioner, targetLevel, targetWcagVersion, date
- Format: date must be ISO 8601; targetLevel must be "A", "AA", or "AAA"
- Content: evaluators must be non-empty list; commissioner must be non-empty string

*For Issue Document*:
- Required: title, sc (non-empty array)
- Format: Each SC must match pattern "X.X.X" (e.g., "1.1.1", "2.4.7")
- Validation: Each SC must exist in reference data; severity/difficulty must be from allowed values
- Content: No markdown syntax validation (allow flexibility)

*Special Checks*:
- Report can have empty scope/out-of-scope (but not required)
- Issues can have empty severity/difficulty (optional fields)
- Multiple issues can reference same SC (allowed)
- Same issue can reference multiple SCs (allowed)

---

### Module 3: Reference Data Manager

**Responsibility**: Manage and provide access to static reference data

**Data Managed**:
- **Success Criteria Definitions** - Number, name, WCAG level for each SC
- **Severity Levels** - Predefined severity classifications
- **Difficulty Levels** - Predefined difficulty classifications
- **Language Translations** - UI strings in multiple languages

**Inputs**:
- Language selection (e.g., "en", "sv")
- SC reference code (e.g., "1.1.1")
- String key to translate (e.g., "label.severity")

**Processing**:
- Load reference data appropriate for selected language
- Provide lookup functions:
  - `getSCDefinition(number, language)` → {number, name, level}
  - `getSeverityLabel(severity, language)` → string
  - `getDifficultyLabel(difficulty, language)` → string
  - `translate(key, language)` → string

**Outputs**:
- Reference data for integration into other modules

**Implementation Notes**:
- Support multiple languages through configuration
- Provide fallback if translation missing (e.g., use English)
- Make easy to add new languages
- Cache or pre-load reference data for performance

**Suggested Data Structure** (Language-Agnostic):
```
ReferenceData/
├── en/
│   ├── success-criteria.json (SC definitions)
│   ├── severity-levels.json
│   ├── difficulty-levels.json
│   └── labels.json (UI strings)
├── sv/
│   ├── success-criteria.json
│   ├── severity-levels.json
│   ├── difficulty-levels.json
│   └── labels.json
└── ...
```

---

### Module 4: Report Organizer

**Responsibility**: Organize and prepare data for rendering

**Inputs**:
- Parsed and validated report data
- Parsed and validated issues
- Reference data (SC definitions, labels)

**Processing**:
1. Group issues by WCAG guideline:
   - Extract guideline number from each SC (e.g., "1.1" from "1.1.1")
   - Group all issues by guideline
   - Within each guideline, group by specific SC

2. Sort organization:
   - Guidelines sorted by number (1, 1.1, 1.2, 2, 2.1, etc.)
   - Issues within each SC sorted by severity (critical → minor)
   - Alphabetically if same severity

3. Enrich with metadata:
   - Add SC name and definition to each issue
   - Add severity/difficulty labels (translated)
   - Calculate statistics:
     - Total issues count
     - Issues by severity
     - Issues by WCAG level
     - Issues by guideline

4. Build navigation structure:
   - Generate table of contents outline
   - Create navigation links
   - Prepare section outlines

**Outputs**:
- Organized report structure ready for rendering:
  ```
  {
    metadata: {...report metadata...},
    statistics: {...calculated stats...},
    guidelines: [
      {
        number: "1.1",
        name: "Text Alternatives",
        level: "A",
        successCriteria: [
          {
            number: "1.1.1",
            name: "Non-text Content",
            level: "A",
            issues: [
              {...issue with enriched data...},
              {...issue with enriched data...}
            ]
          }
        ]
      },
      ...
    ]
  }
  ```

---

### Module 5: HTML Report Renderer

**Responsibility**: Generate the HTML report

**Inputs**:
- Organized report data (from Report Organizer)
- HTML templates
- Language configuration
- CSS styling

**Processing**:
1. Render document structure:
   - HTML header with metadata
   - Semantic HTML tags (nav, section, article, h1-h6)
   - Proper heading hierarchy

2. Render report sections:
   - Title and overview
   - Audit metadata (evaluators, date, etc.)
   - Conformance statement
   - Scope (in-scope and out-of-scope items)
   - Testing environment and tools
   - Technologies evaluated

3. Render issues:
   - For each guideline section
   - For each SC within guideline
   - For each issue:
     - Title with severity indicator
     - Problem description (markdown content rendered as HTML)
     - Solution suggestions
     - References/links

4. Render navigation:
   - Table of contents (using heading structure)
   - Navigation sidebar or menu
   - Links between sections

5. Apply styling:
   - CSS for layout, colors, typography
   - Print styling for PDF
   - Responsive design for different screen sizes
   - Accessibility styling (focus indicators, colors)

6. Embed metadata:
   - Document title and description
   - Author information
   - Creation date
   - Language specification

**Outputs**:
- Single HTML file or multiple interconnected pages
- Fully valid, semantic HTML
- Accessible to users with disabilities
- Print-friendly styling

**Technical Considerations**:
- Use semantic HTML tags for structure (not just divs)
- Ensure proper heading hierarchy
- Include alt text for any images
- Provide sufficient color contrast
- Ensure keyboard navigation works
- Test with screen readers

---

### Module 6: PDF Generator

**Responsibility**: Convert HTML to accessible PDF

**Inputs**:
- HTML report (from HTML Renderer)
- PDF configuration (paper size, margins, etc.)
- PDF/UA-1 metadata

**Processing**:
1. Configure PDF output:
   - Paper size (typically A4 or Letter)
   - Margins and spacing
   - Header/footer if desired

2. Convert HTML to PDF:
   - Preserve semantic structure
   - Convert heading tags to PDF outline
   - Maintain links and navigation

3. Add PDF/UA-1 accessibility features:
   - Tag the PDF (add semantic tags)
   - Create document outline
   - Add metadata (title, author, creation date)
   - Ensure text is selectable (not image-based)
   - Verify screen reader compatibility

4. Generate final PDF:
   - Ensure readability
   - Optimize file size if needed
   - Create bookmarks for navigation

**Outputs**:
- PDF/UA-1 compliant file
- Readable by all PDF readers
- Accessible to screen reader users
- Suitable for printing and archiving

**Important**: Use libraries that specifically support PDF/UA-1 or equivalent accessibility standards. Do not generate PDFs as images—text must remain selectable.

---

### Module 7: Configuration Manager

**Responsibility**: Manage system-level settings

**Configuration Options**:
- Language selection (which language-specific data to use)
- Report metadata defaults (organization name, etc.)
- PDF generation options (paper size, margins)
- Template paths (where to find HTML templates)
- Input/output directories
- Styling preferences

**Implementation**:
- Load from configuration file (JSON, YAML, TOML, etc.)
- Allow command-line overrides
- Provide sensible defaults for all options
- Validate configuration on load

---

## Implementation Approach

### Phase 1: Core Infrastructure
1. Implement Module 1 (Input Parser) - read and parse markdown files
2. Implement Module 2 (Validator) - ensure data quality
3. Implement Module 3 (Reference Data) - manage static data
4. Create test data with sample audit files

### Phase 2: Data Organization
1. Implement Module 4 (Report Organizer) - prepare data
2. Implement Module 7 (Configuration Manager) - settings management
3. Create integration between modules

### Phase 3: Output Generation
1. Implement Module 5 (HTML Renderer) - create HTML reports
2. Create HTML templates and styling
3. Test HTML output with browsers and screen readers

### Phase 4: PDF Generation
1. Implement Module 6 (PDF Generator) - create PDF reports
2. Verify PDF/UA-1 accessibility compliance
3. Test with PDF readers and screen readers

### Phase 5: Polish and Distribution
1. Handle edge cases and errors
2. Create user documentation
3. Package for distribution

## Suggested Technology Choices (Examples Only)

These are examples of what exists in different ecosystems. Choose based on your preference.

**Markdown Parsing**:
- Python: `python-frontmatter`, `markdown`
- Node.js: `front-matter`, `markdown-it`
- Ruby: `front_matter_parser`, `kramdown`
- Go: `yaml`, `markdown` libraries
- Java: `snakeyaml`, `flexmark`

**HTML Templating**:
- Python: Jinja2, Django templates
- Node.js: Nunjucks, EJS, Handlebars
- Ruby: ERB, Liquid
- Go: `html/template`
- Java: Thymeleaf, Freemarker

**PDF Generation (with accessibility)**:
- Python: WeasyPrint, ReportLab
- Node.js: Puppeteer + print-to-PDF, Paged.js
- Ruby: Prawn, WickedPDF
- Go: wkhtmltopdf
- Java: iText, Apache PDFBox

**Note**: Choose libraries that support PDF accessibility/UA compliance. Not all PDF libraries are equal in this regard.

## Testing Checklist

Before considering implementation complete:

### Functionality
- [ ] Parse all valid markdown formats correctly
- [ ] Validate all data fields as specified
- [ ] Organize issues by SC correctly
- [ ] Calculate statistics accurately
- [ ] Generate HTML without errors
- [ ] Generate PDF without errors

### Accessibility
- [ ] HTML is semantic and valid
- [ ] Heading hierarchy is correct
- [ ] All interactive elements are keyboard accessible
- [ ] Proper color contrast
- [ ] Works with screen readers (test with NVDA, JAWS, or VoiceOver)
- [ ] PDF is readable by screen readers
- [ ] PDF/UA-1 validation passes

### Usability
- [ ] Report is easy to navigate
- [ ] Table of contents works
- [ ] Links are clear and functional
- [ ] Print output is readable
- [ ] Mobile/responsive layout works
- [ ] Metadata displays correctly

### Robustness
- [ ] Handles missing optional fields
- [ ] Provides helpful error messages for validation failures
- [ ] Recovers gracefully from malformed input
- [ ] Works with various file encodings
- [ ] Supports multiple languages correctly

## Maintenance and Extension

Once implemented:
- **Add Languages**: Create new language data files in reference data
- **Customize Styling**: Modify CSS to match organization branding
- **Extend Data**: Add new severity/difficulty classifications if needed
- **Integrate**: Connect with other tools (issue trackers, accessibility databases)
- **Automate**: Create build scripts to auto-generate reports from source

## Deployment Options

Your implementation can be deployed as:
- **Command-line tool** - Run locally to generate reports
- **Web application** - Upload markdown files through web interface
- **Build tool plugin** - Integration with build systems (npm, gradle, etc.)
- **CI/CD service** - Automatic report generation on code commits
- **Hosted service** - SaaS solution for organizations
