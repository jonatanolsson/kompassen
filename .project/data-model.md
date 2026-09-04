# IDRC WCAG Reporter - Data Model

## Input Data Structure

### 1. Report Index (Main Report Document)

The audit report is defined through a markdown file with frontmatter metadata and body content.

**Location**: `src/report/index.md` (or similar)

**Structure:**
```yaml
---
# Required Fields
title: "Website Name (Scope Description)"
evaluators:
  - Name of Auditor
  - Another Auditor Name
commissioner: Organization Name
targetLevel: "AA"  # Or "A", "AAA"
targetWcagVersion: "2.1"  # WCAG version (e.g., "2.0", "2.1")
date: 2025-11-12T14:11:00

# Optional Fields
specialRequirements: "Additional context or special requirements for this audit"

# Scope Definition - what was evaluated
scope:
  - title: "Page/Component Name"
    url: "https://example.com/page"
    description: "Description of what was tested"
  - title: "Another Section"
    url: "https://example.com/other"
    description: "What was evaluated"

# Out of Scope - what was NOT evaluated
outOfScope:
  - title: "Component/Area Name"
    url: "https://example.com/excluded"
  - title: "Video Content"
    url: "https://example.com/videos"

# Testing Environment
tools:
  - name: "Mozilla Firefox"
    version: "123"
  - name: "Google Chrome"
    version: "120"
  - name: "NVDA"
    version: "2023.4"
  - name: "VoiceOver"
    version: ""

# Technologies Evaluated
technologies:
  - "HTML5"
  - "CSS"
  - "JavaScript"
  - "Vue.js"
  - "WAI-ARIA"
---

# Report Content (Markdown Body)

The main findings summary, conformance statement, and overview information goes here as regular markdown content.
```

### 2. Issue Records (Individual Findings)

Each accessibility issue is documented in its own markdown file.

**Location**: `src/report/issues/*.md` (one file per issue)

**Structure:**
```yaml
---
# Required Fields
title: "Brief Issue Title"
sc: ["1.1.1", "1.3.1"]  # List of affected WCAG Success Criteria

# Optional but Recommended
severity: "critical"  # or "major", "moderate", "minor"
difficulty: "medium"  # or "easy", "hard"
sample: "all"  # or "some", indicator of scope of problem
screenshots: null  # or URL to screenshots/evidence

---

## Problem

Detailed description of the accessibility issue. Include:
- What is wrong
- Where the problem occurs
- Why it's a problem for users
- Any code examples or technical details

## Solution Suggestions

Potential approaches to fix the issue:
- Solution approach 1
- Solution approach 2
- Best practice recommendations

## Additional Resources

- Links to relevant guidelines
- Tools or references for remediation
- External resources

```

### 3. Reference Data

Static data that provides context and definitions.

#### Success Criteria Data
**Format**: Structured list of WCAG success criteria
**Content**: 
```
Success Criteria #, Success Criteria Name, WCAG Level (A/AA/AAA)
```

**Example**:
```
1.1.1, Non-text Content, A
1.2.1, Audio-only and Video-only (Prerecorded), A
1.3.1, Info and Relationships, A
1.4.3, Contrast (Minimum), AA
2.1.1, Keyboard, A
2.4.7, Focus Visible, AA
```

#### Severity Levels
Predefined severity classifications for issues:
- **Critical** - Complete barrier to access; serious legal/compliance risk
- **Major** - Significant usability problem; affects multiple users
- **Moderate** - Notable issue but with potential workarounds
- **Minor** - Small problem; does not significantly impact users

#### Difficulty Levels
Predefined classifications for remediation effort:
- **Easy** - Can be fixed quickly (hours or less)
- **Medium** - Moderate effort required (days of development)
- **Hard** - Significant effort required (weeks of development)

### 4. Configuration

System-level settings:

**Language Selection**
- Determines which language's UI labels and SC definitions to use
- Options: English, Swedish, others
- Maps to language-specific reference data files

**Report Metadata**
- Base URL for generated reports
- Document title/author information
- PDF generation options (paper size, margins)
- Theme/styling preferences

## Output Data Structure

### HTML Report Output

**Structure:**
```
report.html (or index.html)
├── HTML <head>
│   ├── Title, metadata
│   ├── Accessibility attributes
│   └── Styling references
│
├── HTML <body>
│   ├── Report Navigation
│   │   └── Table of Contents
│   │
│   ├── Report Header
│   │   ├── Title and Basic Info
│   │   ├── Audit Metadata (evaluators, date)
│   │   └── Conformance Statement
│   │
│   ├── Scope Section
│   │   ├── In-Scope Items
│   │   └── Out-of-Scope Items
│   │
│   ├── Testing Information
│   │   ├── Tools Used
│   │   ├── Browsers Tested
│   │   ├── Assistive Technologies
│   │   └── Technologies Evaluated
│   │
│   └── Issues Section
│       └── For each WCAG Guideline
│           └── For each Issue
│               ├── Issue Title and Severity
│               ├── Affected SC
│               ├── Problem Description
│               ├── Solutions
│               └── References
```

### PDF Report Output

Same semantic structure as HTML but:
- Rendered as PDF/UA-1 (universally accessible PDF format)
- Optimized for printing
- Page breaks and numbering
- Bookmarks for navigation
- Metadata embedded for screen reader compatibility

## Data Relationships

```
Report (1)
├── has many Issues (N)
│   └── references many Success Criteria (M)
│       └── has metadata (Name, Level, Definition)
│
├── has Scope Information
│   ├── In-Scope Items (N)
│   └── Out-of-Scope Items (N)
│
├── has Testing Information
│   ├── Tools Used (N)
│   ├── Browsers/Assistive Tech (N)
│   └── Technologies Tested (N)
│
└── has Metadata
    ├── Evaluators (N)
    ├── Commissioner
    ├── Target WCAG Level
    └── Evaluation Date

Issue (1)
├── has Title
├── has Severity (1 of: Critical, Major, Moderate, Minor)
├── has Difficulty (1 of: Easy, Medium, Hard)
├── references Success Criteria (1+)
└── has Content
    ├── Problem Description
    ├── Solutions
    └── References
```

## Data Validation Rules

### Report Validation
- **Required**: title, evaluators, commissioner, targetLevel, targetWcagVersion, date
- **Format**: evaluators must be non-empty list; date must be valid ISO 8601 format
- **Range**: targetLevel must be "A", "AA", or "AAA"; targetWcagVersion must be valid WCAG version

### Issue Validation
- **Required**: title, sc (non-empty list)
- **Format**: Each SC reference must match pattern "X.X.X" (e.g., "1.1.1", "2.4.7")
- **Existence**: Each referenced SC must exist in Success Criteria reference data
- **Severity**: If provided, must be one of the predefined severity levels
- **Difficulty**: If provided, must be one of the predefined difficulty levels

### Content Validation
- All markdown content must be valid markdown syntax
- No required markdown structure enforced (allows flexibility)

## Example: Complete Data Sample

**Input: index.md (Report)**
```yaml
---
title: "Example Corporation Website"
evaluators:
  - Jane Auditor
commissioner: "Example Corp"
targetLevel: "AA"
targetWcagVersion: "2.1"
date: 2025-05-06
scope:
  - title: "Homepage"
    url: "https://example.com"
outOfScope:
  - title: "Third-party embedded content"
tools:
  - name: "Firefox"
    version: "126"
technologies:
  - "HTML5"
  - "CSS3"
  - "JavaScript"
---

## Conformance Status

The website does **not** fully conform to WCAG 2.1 Level AA.
```

**Input: issues/missing-alt-text.md**
```yaml
---
title: "Missing Alternative Text on Images"
sc: ["1.1.1"]
severity: "major"
difficulty: "easy"
---

## Problem

Images throughout the site lack alt attributes or have empty alt text, 
preventing screen reader users from understanding image content.

## Solution

Add descriptive alt text to all images that convey information.

```

**Output: HTML Structure**
```html
<html>
  <head>
    <title>Example Corporation Website - WCAG Audit Report</title>
  </head>
  <body>
    <nav><h2>Table of Contents</h2>...</nav>
    
    <section id="overview">
      <h1>WCAG Audit Report: Example Corporation Website</h1>
      <p>Evaluators: Jane Auditor</p>
      <p>Conformance: Not Fully Conformant</p>
    </section>
    
    <section id="issues">
      <h2>Issues by Guideline</h2>
      <section id="sc-1.1">
        <h3>1.1 Text Alternatives</h3>
        <article id="issue-missing-alt">
          <h4>1.1.1 Missing Alternative Text on Images</h4>
          <p><strong>Severity:</strong> Major</p>
          <!-- ... issue content ... -->
        </article>
      </section>
    </section>
  </body>
</html>
```
