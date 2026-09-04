# IDRC WCAG Reporter - Technology-Agnostic Documentation

Welcome to the comprehensive technology-agnostic documentation for the IDRC WCAG Reporter. This documentation describes the system, its purpose, and how to build it—without prescribing any specific technology stack.

## Document Structure

This documentation suite consists of five complementary documents:

### 1. [Project Overview](./overview.md)
**Read this first to understand what this system is.**

Covers:
- What the IDRC WCAG Reporter is
- Its purpose and goals
- Target audience and user personas
- Key features at a glance
- Who benefits and why

**Best for**: Decision makers, project stakeholders, accessibility team leads

---

### 2. [System Architecture](./architecture.md)
**Read this to understand how the system works.**

Covers:
- High-level system design and components
- Data flow through the system
- Core workflows (report generation, audit process)
- Multi-language support architecture
- Design principles

**Best for**: Technical architects, solution designers, system integrators

---

### 3. [Data Model](./data-model.md)
**Read this to understand the data structure.**

Covers:
- Input data format (report index, issues, reference data)
- Output data structure (HTML, PDF)
- Field definitions and validation rules
- Example data samples
- Data relationships and integrity rules

**Best for**: Data analysts, database designers, developers implementing specific modules

---

### 4. [Core Features & Workflows](./features.md)
**Read this to understand what the system does and how it's used.**

Covers:
- Detailed description of each core feature
- How features are used in practice
- User workflows (auditing, remediation, compliance)
- Practical examples and use cases
- Key considerations and constraints

**Best for**: Product managers, UX designers, business analysts, end users

---

### 5. [Implementation Guide](./implementation-guide.md)
**Read this to understand what to build.**

Covers:
- Technology-agnostic system requirements
- Seven core modules to implement
- What each module does and how it integrates
- Five-phase implementation approach
- Testing and deployment considerations
- Example technology choices (not prescriptive)

**Best for**: Software architects, development teams, implementation leads

---

## How to Use This Documentation

### For Different Roles

**Accessibility Manager/Team Lead**
1. Start with [overview.md](./overview.md) to understand the system's purpose
2. Review [features.md](./features.md) to understand capabilities
3. Use [data-model.md](./data-model.md) as reference when working with auditors

**Software Architect**
1. Read [architecture.md](./architecture.md) to understand system design
2. Study [implementation-guide.md](./implementation-guide.md) for module specifications
3. Reference [data-model.md](./data-model.md) for data contracts

**Development Team**
1. Review [implementation-guide.md](./implementation-guide.md) for what to build
2. Use [data-model.md](./data-model.md) for input/output specifications
3. Reference [architecture.md](./architecture.md) for integration points
4. Check [features.md](./features.md) to understand user expectations

**Web Accessibility Auditor**
1. Start with [overview.md](./overview.md) to understand the tool
2. Review [features.md](./features.md) to learn about workflows
3. Reference [data-model.md](./data-model.md) for input format details

**Project Stakeholder/Decision Maker**
1. Read [overview.md](./overview.md) for business case
2. Skim [features.md](./features.md) for capability overview
3. Review [implementation-guide.md](./implementation-guide.md) scope to understand effort

---

## Key Concepts

### WCAG Compliance
The system is designed around WCAG (Web Content Accessibility Guidelines), an international standard for web accessibility. All findings are organized around WCAG Success Criteria (SC), numbered like 1.1.1, 2.4.7, etc.

### Markdown-Based
Audit findings are documented in simple markdown files with YAML frontmatter (metadata). This enables:
- Easy editing in any text editor
- Version control in Git
- Collaboration among auditors
- Maintaining consistency

### Three-Stage Pipeline
The system processes data in three stages:
1. **Input** - Markdown files with audit findings and metadata
2. **Processing** - Validation, organization, enrichment
3. **Output** - Professional HTML and PDF/UA-1 reports

### Technology-Agnostic Design
All components are described in technology-neutral terms. You can implement this system in any modern programming language or framework:
- Python, Node.js, Ruby, Go, Java, C#, PHP, Rust, etc.
- Web frameworks, CLI tools, desktop applications
- Cloud services, SaaS platforms, or local installation

The documentation describes *what to build* and *why*, not *how to build it with technology X*.

---

## Getting Started

### If You Want to Understand the System
1. Read [overview.md](./overview.md) (15 min)
2. Read [features.md](./features.md) (25 min)
3. Browse [architecture.md](./architecture.md) (20 min)

### If You Want to Implement the System
1. Read [overview.md](./overview.md) (15 min)
2. Study [implementation-guide.md](./implementation-guide.md) (30 min)
3. Reference [data-model.md](./data-model.md) throughout implementation (ongoing)
4. Review [architecture.md](./architecture.md) for integration guidance (20 min)

### If You Want to Use the System
1. Read [overview.md](./overview.md) (15 min)
2. Review [features.md](./features.md) sections on user workflows (20 min)
3. Reference [data-model.md](./data-model.md) when creating audit documents (ongoing)

---

## Documentation Principles

These documents are written with these principles:

1. **Technology-Agnostic** - No prescription of specific languages, frameworks, or tools
2. **Complete** - Sufficient detail that someone could implement the system independently
3. **Clear** - Plain language explaining both "what" and "why"
4. **Structured** - Organized for different audiences and use cases
5. **Practical** - Includes examples, workflows, and use cases
6. **Extensible** - Designed with customization and extension in mind

---

## Key Facts About the System

- **Purpose**: Transform accessibility audit findings (markdown) into professional reports (HTML/PDF)
- **Users**: Web accessibility auditors, QA teams, compliance officers, developers
- **Input Format**: Markdown files with YAML frontmatter containing findings metadata
- **Output Formats**: Professional HTML and PDF/UA-1 reports
- **Organization Principle**: WCAG Success Criteria (1.1.1, 2.4.7, etc.)
- **Key Features**: Automatic organization, severity/difficulty classification, multi-language support
- **Benefit**: Standardized, professional accessibility reports with minimal manual formatting

---

## About This Documentation

**Created**: As a technology-agnostic specification for the IDRC WCAG Reporter

**Purpose**: Enable anyone to understand the system and implement it independently in their technology of choice

**Scope**: Core report generation system (excluding GitHub integration, advanced customization)

**Language**: English, with assumptions that readers understand WCAG standards

---

## Questions or Feedback?

This documentation is maintained as part of the IDRC WCAG Reporter project. For:
- **Questions about the system**: Review relevant documentation section
- **Implementation questions**: Check [implementation-guide.md](./implementation-guide.md)
- **Data format questions**: Reference [data-model.md](./data-model.md)
- **Feature questions**: Review [features.md](./features.md)

---

**Last Updated**: 2025-05-06

**Documentation Version**: 1.0
