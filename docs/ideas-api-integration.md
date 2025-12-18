# Ideas API Integration

This document describes the integration with the Ideas API for generating landing page ideas through AI-powered conversations.

## Overview

The Ideas API integration allows users to generate and iterate on landing page ideas through a conversational interface. Each iteration creates a new version of the page idea, maintaining a complete history of changes.

## Architecture

### Data Model

The versioning system uses the following approach:

1. **Conversations**: Each user has conversations that contain the full chat history
2. **Messages**: Individual messages in the conversation (user or assistant)
3. **Page Ideas**: Generated landing page ideas associated with assistant messages
4. **Versioning**: Each new generation creates a new PageIdea record, linked to the conversation

### Key Components

- `IdeasApiService`: Handles communication with the external Ideas API
- `AIService`: Orchestrates the generation process and manages versioning
- `PageIdeaController`: Handles HTTP requests for the page idea interface
- `PageIdea` Model: Enhanced with versioning methods

## Configuration

Add the following environment variables to your `.env` file:

```bash
# Ideas API Configuration
IDEAS_API_BASE_URL=http://localhost:3000
IDEAS_API_KEY=your-api-key-here

# For production
IDEAS_API_BASE_URL=https://example.com
```

## Usage

### 1. Access the Page Idea Generator

Navigate to `/page-ideas/create` to access the page idea generation interface.

### 2. Generate Page Ideas

1. Enter your initial request (e.g., "Create a landing page for my SaaS product")
2. Click "Generate Page Idea"
3. Review the generated idea
4. Provide feedback to iterate (e.g., "Make it more focused on enterprise customers")
5. Generate new versions as needed

### 3. View Version History

The interface shows:

- Current conversation history
- Latest page idea version
- Complete version history with ability to compare

## API Endpoints

### Generate Page Idea

```
POST /page-ideas/generate
```

**Request:**

```json
{
    "conversation_id": 1,
    "message": "Create a landing page for my business"
}
```

**Response:**

```json
{
    "success": true,
    "message": {
        /* Message object */
    },
    "pageIdea": {
        /* PageIdea object */
    },
    "conversation": {
        /* Updated conversation */
    }
}
```

### Get Version History

```
GET /conversations/{conversation}/page-ideas/versions
```

### Test API Connection

```
GET /page-ideas/test-connection
```

## Versioning System

### How It Works

1. **First Generation**: Creates conversation, user message, assistant message, and first PageIdea
2. **Subsequent Generations**: Adds new user message, assistant message, and new PageIdea
3. **Version Tracking**: Each PageIdea knows its version number and can navigate to previous/next versions

### Version Methods

The `PageIdea` model provides several versioning methods:

- `getVersionNumber()`: Returns the version number within the conversation
- `isLatestVersion()`: Checks if this is the most recent version
- `getPreviousVersion()`: Gets the previous version
- `getNextVersion()`: Gets the next version

### Example Usage

```php
$pageIdea = PageIdea::find(1);
$versionNumber = $pageIdea->getVersionNumber(); // e.g., 3
$isLatest = $pageIdea->isLatestVersion(); // true/false
$previous = $pageIdea->getPreviousVersion(); // PageIdea or null
```

## Testing

### Run Tests

```bash
php artisan test --filter=PageIdeaGenerationTest
```

### Test API Connection

```bash
php artisan ideas-api:test-connection
```

## Error Handling

The system handles various error scenarios:

1. **API Connection Issues**: Shows connection status in the UI
2. **Authentication Errors**: Proper error messages for invalid API keys
3. **Validation Errors**: Handles malformed responses from the Ideas API
4. **Network Errors**: Graceful handling of timeouts and connection failures

## Security

- API keys are stored securely in environment variables
- Users can only access their own conversations and page ideas
- All API communication uses HTTPS in production
- Input validation prevents malicious requests

## Future Enhancements

1. **Export Functionality**: Export page ideas to various formats
2. **Collaboration**: Allow multiple users to work on the same conversation
3. **Templates**: Pre-built conversation starters for common use cases
4. **Analytics**: Track usage patterns and popular page idea types
5. **Integration**: Connect generated ideas directly to website creation

## Troubleshooting

### Common Issues

1. **API Connection Failed**

    - Check if the Ideas API service is running
    - Verify the API key is correct
    - Ensure the base URL is accessible

2. **Generation Fails**

    - Check the Laravel logs for detailed error messages
    - Verify the Ideas API is responding correctly
    - Test the connection using the Artisan command

3. **Version History Issues**
    - Ensure the database migrations have been run
    - Check that the polymorphic relationships are set up correctly

### Debug Commands

```bash
# Test API connection
php artisan ideas-api:test-connection

# Check configuration
php artisan config:show services.ideas_api

# View logs
tail -f storage/logs/laravel.log
```
