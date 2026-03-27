# YouTube Course Scraper

## Overview

**YouTube Course Scraper** is a Laravel-based web application that discovers educational YouTube playlists (courses) automatically using AI-generated search queries. Users can submit high-level topics or categories (e.g., Programming, Marketing, Engineering) into a custom Arabic Right-to-Left (RTL) interface. 

The application utilizes the **Laravel AI SDK (Gemini)** to transform these generalized categories into specific, highly relevant course search terms in both Arabic and English. It then interacts with the **YouTube Data API v3** to discover and persist the top matching YouTube playlists into the database, handling duplicates seamlessly. 

Recently, the engine was heavily upgraded to compute deep analytics per playlist (total lessons, cumulative views, and exact ISO 8601 cumulative durations). To support stability, **Repository Pattern Architecture** strictly governs all internal Database IO operations.

---

## 🏗️ Architecture Diagrams

### 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    CATEGORIES ||--o{ CATEGORY_COURSE : "has"
    COURSES ||--o{ CATEGORY_COURSE : "belongs to"
    
    COURSES ||--o{ COURSE_PLAYLIST : "contains"
    PLAYLISTS ||--o{ COURSE_PLAYLIST : "belongs to"

    CATEGORIES {
        bigint id PK
        string name UK
        timestamp created_at
    }

    COURSES {
        bigint id PK
        string title UK
        timestamp created_at
    }

    CATEGORY_COURSE {
        bigint category_id FK
        bigint course_id FK
    }

    PLAYLISTS {
        bigint id PK
        string playlist_id UK "YouTube Unique ID"
        string title
        text description
        string thumbnail
        string channel_name
        int lessons_count
        bigint view_count
        int duration_seconds
        timestamp created_at
    }

    COURSE_PLAYLIST {
        bigint course_id FK
        bigint playlist_id FK
    }
```

### 2. General System Flowchart

```mermaid
flowchart TD
    Start([User Initiates Request]) --> Input[Input Target Educational Categories]
    Input --> Validate{Clean Data?}
    
    Validate -- Yes --> AI[AI Engine Generates Distinct Course Titles]
    Validate -- No --> Return[Show Validation Error]
    
    AI --> LoopCourse[For Each Generated Course Title]
    
    subgraph "YouTube Discovery Pipeline"
        LoopCourse --> SearchYT[Search YouTube Playlists]
        SearchYT --> LimitYT[Extract Top 2 Matches]
        LimitYT --> DeepScan["Deep Scan Analytics\n(Lessons, Run Time, Viewers)"]
    end
    
    DeepScan --> Storage[(Save to Knowledge Base)]
    Storage --> UIRefresh([Populate Interactive Dashboard])
```

### 3. Execution Sequence Diagram

```mermaid
sequenceDiagram
    participant Browser as Web Portal
    participant Platform as Scraping Engine
    participant AI as AI Model (Gemini)
    participant YT as YouTube Network
    participant KB as Knowledge Base

    Browser->>Platform: Submit Educational Categories
    Platform-->>Browser: Accept Request & Process in Background
    
    Note over Platform, KB: Asynchronous Data Gathering Phase
    Platform->>KB: Register Target Category
    
    Platform->>AI: Prompt: Generate relevant Course Titles
    AI-->>Platform: Returns Array of Course Titles
    
    loop Every Course Title
        Platform->>KB: Register Derived Course
        
        Platform->>YT: Search for Best Educational Playlists
        YT-->>Platform: Return Top 2 Playlists Base Data
        
        Platform->>YT: Deep Data Scan (Fetch Videos & Stats)
        YT-->>Platform: Return Run Time, Lesson Count, Views
        
        Platform->>KB: Store Enriched Course Assets & Metrics
    end
    
    Browser->>Platform: Refresh Interactive Dashboard
    Platform-->>Browser: Render Curated Course Catalog
```

---

## 🚀 Setup & Installation Documentation

### Prerequisites
- PHP 8.3+
- Composer
- MySQL Database Engine
- Node.js & NPM
- YouTube Data API v3 Key
- Gemini API Key

### Step-by-Step Installation

1. **Clone the Directory**  
   Clone this repository to your local machine and CD into the destination directory.
   ```bash
   git clone <repository_url>
   cd YoutubeScrapperTask
   ```

2. **Install Dependencies**  
   Install the necessary Composer and NPM requirements.
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup**  
   Copy the example environment file if you haven't already:
   ```bash
   cp .env.example .env
   ```
   Generate application key:
   ```bash
   php artisan key:generate
   ```

4. **Configure the Database**  
   Update your `.env` to match your local database authentication:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=youtube_scrapper
   DB_USERNAME=root
   DB_PASSWORD=YourPassword
   ```

5. **Configure API Keys**  
   At the very bottom of your `.env` file, supply your AI & YouTube credentials. The Application relies on Gemini for NLP logic.
   Google API endpoints and global environment keys are securely mapped in `config/youtube.php`.
   ```env
   GEMINI_API_KEY="your-gemini-key-goes-here"
   YOUTUBE_API_KEY="your-youtube-v3-key-goes-here"
   ```
   *Tip: Remember to navigate to your Google Cloud Console to restrict your API Key.*

6. **Execute Database Migrations**  
   Run the migration command to execute all Database schemas ensuring they fall into correct relational execution steps (including the Pivot Tables and Queue defaults):
   ```bash
   php artisan migrate:fresh
   ```

### 🏃 Running the Project

To execute the project, you need two separate terminal windows. One server will process the web interactions, and the other will process the heavy queued background API requests.

**Terminal 1: Start Laravel Development Web Server**
```bash
php artisan serve
```
Open your browser to `http://127.0.0.1:8000/`.

**Terminal 2: Start The Background Queue Worker**
Because querying the AI and hitting YouTube's endpoints is extremely heavy (with baked-in HTTP throttles & `sleep()` sequences preventing 429 limits), all data collection operates asynchronously inside resilient timeout workers.
```bash
php artisan queue:work
```

### Usage
- Once the application is up, navigate to the web interface. 
- Paste or type in topics/categories inside the prompt area (1 per line).
- Hit **ابدأ الجمع** (Start Fetching). 
- Observe the Queue terminal processing jobs! Refresh the browser once they complete to see your populated customizable grid tiles containing native YouTube links, absolute playtime calculations, and cumulative viewership!
