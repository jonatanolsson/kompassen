# IDRC WCAG Reporter - Core Features & Workflows

> **Current implementation note:** The current workflow is database-driven: users create projects, pages and issues through authenticated Flux/Livewire forms, attach WCAG criteria and testing methodologies, generate HTML/PDF reports, and share project previews. The markdown-file workflows below describe the original product concept and should not be treated as current implementation details.

## Core Features

### Feature 1: Markdown-Based Audit Documentation

**What It Does**: Allows auditors to document accessibility findings in simple, maintainable markdown files with structured metadata.

**User Experience**:
1. Auditor creates a markdown file per finding
2. Adds frontmatter metadata (SC references, severity, difficulty)
3. Writes human-readable problem and solution descriptions
4. System automatically processes and includes in report

**Benefits**:
- Simple format (markdown) that's easy to learn
- Structured metadata enables automatic organization
- Files are versionable (can be stored in git)
- Can be authored in any text editor
- Supports collaborative editing workflows

**Example Use Case**: 
An auditor finds an image without alt text. Instead of manually formatting this finding, they create `issues/missing-alt.md` with the SC reference "1.1.1", severity "major", and problem description. The system automatically handles formatting and placement in the report.

---

### Feature 2: Automatic Report Generation (HTML)

**What It Does**: Transforms markdown audit data into a professionally formatted HTML report with navigation, styling, and semantic structure.

**User Experience**:
1. Auditor runs "generate report" command
2. System reads all audit files
3. System validates and processes content
4. System generates complete HTML report
5. Report is ready to view in browser or share

**Report Includes**:
- Professional styling and layout
- Table of contents with navigation
- Audit metadata display
- Scope and testing information
- Issues organized by WCAG guideline
- Proper semantic HTML for accessibility
- Print-friendly styling

**Benefits**:
- No manual formatting required
- Consistent, professional appearance
- Fully accessible (HTML properly structured)
- Can be viewed in any web browser
- Responsive design works on all devices
- Easy to share via email or web hosting

**Example Output**:
A 50+ page audit report formatted professionally with headers, sections, tables, and lists—all generated automatically from markdown input.

---

### Feature 3: PDF Report Export (PDF/UA-1)

**What It Does**: Converts the HTML report to a universally accessible PDF document suitable for archiving and formal distribution.

**User Experience**:
1. After HTML generation, auditor runs "generate PDF" command
2. System converts HTML to PDF/UA-1 format
3. PDF includes proper accessibility metadata
4. PDF is ready for printing or distribution

**PDF Characteristics**:
- PDF/UA-1 compliant (universal accessibility standard for PDFs)
- Proper semantic structure readable by screen readers
- Document outline/bookmarks for navigation
- Embedded metadata (title, author, creation date)
- Optimized for printing
- Text is selectable (not image-based)

**Benefits**:
- Professional format for formal reporting
- Suitable for archival and compliance documentation
- Meets accessibility requirements (users with disabilities can read)
- Can be printed or distributed electronically
- Compatible with all PDF readers
- Reduced file size compared to equivalent images

**Compliance Aspects**:
- PDF/UA-1 is internationally recognized accessibility standard
- Ensures report itself meets accessibility standards
- Supports screen reader navigation
- Allows copy/paste of text
- Maintains reading order

---

### Feature 4: WCAG Success Criteria Organization

**What It Does**: Automatically organizes all findings by WCAG Success Criteria, providing a standardized structure for understanding conformance.

**How It Works**:
- Each issue references one or more SC (e.g., "1.1.1", "2.4.7")
- System groups issues by SC
- Issues are sorted numerically
- SC definitions and names are automatically included

**User Experience**:
1. Auditor tags each finding with relevant SC numbers
2. System automatically groups findings by WCAG guideline
3. Report shows SC 1.1.1 issues together, then 1.2.1 issues, etc.
4. Reader can easily understand which standards are violated

**Benefits**:
- Provides standardized structure
- Makes report easy to navigate
- Shows exactly which WCAG criteria have issues
- Enables tracking of conformance against specific requirements
- Facilitates remediation prioritization (by SC level: A, AA, AAA)

**Example Structure**:
```
1. Perceivable
  1.1 Text Alternatives
    1.1.1 Non-text Content
      - Issue A (Critical)
      - Issue B (Major)
  1.2 Time-based Media
    1.2.1 Audio-only and Video-only (Prerecorded)
      - Issue C (Moderate)
2. Operable
  2.1 Keyboard Accessible
    2.1.1 Keyboard
      - Issue D (Minor)
```

---

### Feature 5: Severity and Difficulty Classification

**What It Does**: Allows auditors to classify issues by impact severity and remediation difficulty, enabling prioritization.

**Severity Levels** (Impact on Users):
- **Critical**: Complete barrier to access; serious compliance risk
- **Major**: Significant usability problem affecting multiple users
- **Moderate**: Notable issue with potential workarounds
- **Minor**: Small problem with minimal user impact

**Difficulty Levels** (Effort to Fix):
- **Easy**: Can be resolved quickly (hours)
- **Medium**: Moderate development effort required (days)
- **Hard**: Significant effort required (weeks or more)

**Matrix Usage Example**:
```
Critical + Easy = Fix immediately (e.g., add alt text to images)
Critical + Hard = Plan major remediation effort
Minor + Hard = Consider if worth effort
Minor + Easy = Fix as quick wins
```

**Benefits**:
- Enables evidence-based prioritization
- Helps plan remediation roadmap
- Shows quick wins (easy to fix issues)
- Identifies major effort requirements
- Makes business case for resource allocation

**Example Report Section**:
```
Critical Issues (Must Fix):
- Issue A (Easy) - Fix first
- Issue B (Hard) - Plan for development sprint

Major Issues (Should Fix Soon):
- Issue C (Medium) - Plan for near-term

Minor Issues (Consider):
- Issue D (Easy) - Quick win opportunity
```

---

### Feature 6: Comprehensive Audit Metadata

**What It Does**: Captures and displays detailed information about how and when the audit was conducted.

**Metadata Captured**:
- **Evaluators**: Names of auditors who conducted the evaluation
- **Commissioner**: Organization requesting the audit
- **Date**: When the audit was performed
- **Target Level**: WCAG level being evaluated (A, AA, or AAA)
- **WCAG Version**: Which version of WCAG was used (2.0, 2.1, etc.)
- **Scope**: Detailed description of what was evaluated
- **Out of Scope**: Clear statement of what was NOT evaluated
- **Testing Tools**: Browsers, screen readers, and testing tools used
- **Technologies**: Programming languages, frameworks, and techniques evaluated

**Benefits**:
- Provides context for understanding findings
- Enables reproduction of audit
- Meets compliance documentation requirements
- Shows professional, thorough evaluation process
- Helps future auditors understand what was tested
- Supports accessibility statement requirements

**Example**:
```
Audit Metadata:
- Evaluators: Jane Auditor, Bob Specialist
- Tested: Desktop, Tablet, Mobile
- Browsers: Firefox, Chrome, Safari
- Assistive Tech: NVDA, VoiceOver
- Technologies: React, Tailwind CSS, Vue.js
- Date: 2025-05-06
- Target: WCAG 2.1 Level AA
```

---

### Feature 7: Multi-Language Support

**What It Does**: Allows reports to be generated in multiple languages, with automatically translated UI elements and success criteria definitions.

**Supported Translations**:
- WCAG Success Criteria names and definitions
- UI labels and headings
- Section titles and descriptions
- Content warnings or special notices

**How It Works**:
- System includes language-specific reference data files
- During report generation, user selects target language
- All SC definitions and labels use language-appropriate text
- Markdown content (problem, solutions) remains as authored

**Benefits**:
- Global accessibility (reports understandable in local language)
- Supports international organizations
- Enables serving diverse stakeholder groups
- Maintains professional standards across languages
- Easily extensible to new languages

**Example**: Same audit report can be generated in English for US auditors and Swedish for Swedish stakeholders, both understanding the WCAG criteria.

---

## User Workflows

### Workflow 1: Conducting and Documenting an Accessibility Audit

**Participants**: Web Accessibility Auditor

**Steps**:

1. **Preparation**
   - Define audit scope (which pages/features)
   - Identify WCAG level to evaluate against
   - Select testing tools (browsers, screen readers, testing utilities)

2. **Create Report Index**
   - Create main report file with audit metadata
   - Document scope (in-scope and out-of-scope)
   - List tools and technologies tested
   - Add initial conformance statement

3. **Conduct Testing**
   - Test website/application systematically
   - Document each finding as you discover it
   - Note WCAG success criteria affected
   - Assess severity and difficulty

4. **Document Each Finding**
   - Create markdown file for each issue
   - Add frontmatter: SC references, severity, difficulty
   - Write clear problem description
   - Suggest potential solutions
   - Add references and resources

5. **Generate Report**
   - Run system to validate all files
   - Generate HTML report
   - Generate PDF report
   - Review output for accuracy

6. **Distribution**
   - Share HTML report via web link or email
   - Share PDF report for formal documentation
   - Both are fully accessible to users with disabilities

---

### Workflow 2: Remediation Planning Using Report

**Participants**: Project Manager, Development Team

**Steps**:

1. **Receive Report**
   - Review audit report (HTML or PDF)
   - Understand overall conformance status
   - Review scope and limitations

2. **Review Severity Matrix**
   - Identify critical issues (must fix)
   - Identify major issues (should fix soon)
   - Identify minor issues (consider)

3. **Assess Effort**
   - Review difficulty classification
   - Identify quick wins (easy fixes)
   - Plan major efforts (hard fixes)

4. **Create Roadmap**
   - Prioritize critical + easy issues
   - Plan critical + hard issues for development
   - Schedule additional testing if needed

5. **Track Remediation**
   - Use report as checklist
   - Cross-reference issues with development tickets
   - Plan re-evaluation after fixes

---

### Workflow 3: Regulatory Compliance Reporting

**Participants**: Compliance Officer, Legal Team

**Steps**:

1. **Collect Audit Report**
   - Receive completed audit report
   - Verify report metadata (dates, evaluators)
   - Check that report is properly formatted

2. **Document Conformance Status**
   - Extract conformance statement from report
   - Note any partial conformance items
   - Document WCAG level and version

3. **Create Accessibility Statement**
   - Use report findings to populate accessibility statement
   - Reference known issues (from report)
   - Document remediation plans

4. **Archive**
   - Store PDF report for regulatory compliance
   - PDF/UA-1 compliance meets accessibility requirement
   - Metadata allows future searches

5. **Review at Intervals**
   - Plan next audit cycle
   - Re-run evaluation against updated standards
   - Document accessibility improvement progress

---

### Workflow 4: Remediation and Re-evaluation

**Participants**: Development Team, Accessibility Specialist

**Steps**:

1. **Fix Issues**
   - Prioritize based on severity/difficulty
   - Implement fixes for identified issues
   - Test fixes locally

2. **Request Re-evaluation**
   - Provide updated website to auditor
   - Document changes made

3. **Conduct Follow-up Audit**
   - Test fixed areas
   - Create updated report
   - Document which issues are now resolved
   - Identify any new issues

4. **Update Compliance Status**
   - Generate new report with improved conformance
   - Share progress with stakeholders
   - Update accessibility statement

---

## Key Considerations

### Report Accuracy
- Report is only as accurate as the audit
- System does not perform the audit itself
- Auditor responsibility to correctly identify and classify issues

### Scope Limitations
- Report documents what was evaluated
- Out-of-scope items not covered
- Clear communication of scope is critical

### Maintenance
- Report should be updated when site is significantly changed
- Continuous improvement requires periodic re-evaluation
- Issues should track fixes when remediated

### Usage of Reports
- Reports are meant to be shared with stakeholders
- Both HTML and PDF formats support wide distribution
- Proper metadata supports compliance documentation
