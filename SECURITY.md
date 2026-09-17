# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

The PassVault team takes security bugs seriously. We appreciate your efforts to responsibly disclose your findings.

**Please do NOT report security vulnerabilities through public GitHub issues.**

### How to Report

Send an email to **dereklevilla45@gmail.com** with:

- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### What to Expect

- **Acknowledgment**: Within 48 hours of your report
- **Assessment**: We'll evaluate the severity and impact
- **Updates**: We'll keep you informed of progress toward a fix
- **Credit**: We'll credit reporters who help improve security (unless you prefer anonymity)

## Security Best Practices

### For Users

- Keep your installation up to date
- Use strong, unique master passwords
- Enable two-factor authentication (TOTP)
- Use HTTPS in production
- Regularly backup your database
- Restrict file permissions on `.env`

### For Developers

- Never commit secrets or API keys
- Use Laravel's built-in security features
- Validate and sanitize all inputs
- Use parameterized queries
- Implement proper CSRF protection
- Follow the principle of least privilege

## Scope

This policy covers:
- The PassVault Laravel application
- The browser extension
- API endpoints
- Authentication and authorization
- Data encryption and storage

## Out of Scope

- Third-party dependencies (report to their maintainers)
- Social engineering attacks
- Physical attacks
- Denial of service attacks

Thank you for helping keep PassVault and its users safe!
