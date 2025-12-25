# Elasticsearch Setup Guide

## Prerequisites

You need to install and run Elasticsearch for this application to work.

## Installation Steps

### Option 1: Download and Install Elasticsearch (Recommended)

1. **Download Elasticsearch**
   - Visit: https://www.elastic.co/downloads/elasticsearch
   - Download the version compatible with your OS (Linux/Windows/Mac)
   - Extract the downloaded file

2. **For Linux/Mac:**
   ```bash
   # Extract the archive
   tar -xzf elasticsearch-8.x.x-linux-x86_64.tar.gz
   cd elasticsearch-8.x.x
   
   # Start Elasticsearch
   ./bin/elasticsearch
   ```

3. **For Windows:**
   - Extract the ZIP file
   - Navigate to the extracted folder
   - Run `bin\elasticsearch.bat`

4. **Verify Installation**
   - Open browser and go to: http://localhost:9200
   - You should see JSON response with cluster information

### Option 2: Using Docker (Easier)

```bash
# Pull and run Elasticsearch
docker pull docker.elastic.co/elasticsearch/elasticsearch:8.11.0
docker run -d -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:8.11.0
```

### Option 3: Using Package Manager (Linux)

**Ubuntu/Debian:**
```bash
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
sudo apt-get install apt-transport-https
echo "deb https://artifacts.elastic.co/packages/8.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-8.x.list
sudo apt-get update && sudo apt-get install elasticsearch
sudo systemctl start elasticsearch
sudo systemctl enable elasticsearch
```

**CentOS/RHEL:**
```bash
sudo rpm --import https://artifacts.elastic.co/GPG-KEY-elasticsearch
sudo yum install elasticsearch
sudo systemctl start elasticsearch
sudo systemctl enable elasticsearch
```

## Create Indexes

After Elasticsearch is running, you need to create the indexes with their mappings.

### Method 1: Using curl commands

```bash
# Create index 10 (Administrators)
curl -X PUT "localhost:9200/10" -H 'Content-Type: application/json' -d'
{
  "mappings": {
    "dynamic": "false",
    "properties": {
      "id": {"type": "integer"},
      "fullName": {
        "type": "text",
        "fields": {
          "sortable": {"type": "keyword", "ignore_above": 30000, "normalizer": "case_insensitive"},
          "untouched": {"type": "keyword"},
          "keyword": {"type": "keyword"}
        },
        "analyzer": "standard"
      },
      "userName": {"type": "keyword"},
      "email": {
        "type": "text",
        "fields": {
          "sortable": {"type": "keyword", "ignore_above": 30000, "normalizer": "case_insensitive"},
          "untouched": {"type": "keyword"}
        },
        "analyzer": "standard"
      },
      "phno": {"type": "long"},
      "password": {"type": "keyword"},
      "updationDate": {"type": "date", "format": "yyyy-MM-dd HH:mm:ss"}
    }
  }
}'

# Create other indexes similarly...
```

### Method 2: Using PHP Script

Run the `create_indexes.php` script (see below)

### Method 3: Using Kibana Dev Tools

1. Install Kibana (optional but helpful)
2. Open Kibana Dev Tools
3. Paste the mapping JSON from `elasticsearch_mappings_numeric.json`

## Migrate Data from MySQL

After creating indexes, you need to migrate your existing MySQL data to Elasticsearch.

### Option 1: Use Migration Script

Create a migration script to copy data from MySQL to Elasticsearch (see `migrate_data.php` example)

### Option 2: Manual Migration

You can manually insert data using curl or the Elasticsearch API.

## Verify Setup

1. Visit: http://localhost:9200
2. Visit: http://localhost:9200/_cat/indices?v (to see all indexes)
3. Run: `test_elasticsearch.php` in your browser

## Troubleshooting

### Elasticsearch won't start
- Check if port 9200 is already in use: `netstat -an | grep 9200`
- Check Elasticsearch logs in `logs/` directory
- Ensure you have Java installed (Elasticsearch requires Java)

### Connection refused
- Ensure Elasticsearch is running
- Check firewall settings
- Verify `ES_HOST` and `ES_PORT` in `config_elasticsearch.php`

### Index not found
- Create the index first using the mapping file
- Check index name matches (should be numeric: 1, 2, 3, etc.)

## System Requirements

- **Java**: Elasticsearch requires Java 11 or higher
- **Memory**: At least 2GB RAM recommended
- **Disk Space**: ~500MB for Elasticsearch installation

## Quick Start Checklist

- [ ] Install Java (if not already installed)
- [ ] Download and install Elasticsearch
- [ ] Start Elasticsearch service
- [ ] Verify connection: http://localhost:9200
- [ ] Create indexes using mappings
- [ ] Migrate data from MySQL
- [ ] Test login functionality

## Notes

- Elasticsearch runs on port 9200 by default
- The application uses numeric index names (1-30) as defined in the mappings
- All indexes use `dynamic: false` for strict schema enforcement
- Date formats follow: `yyyy-MM-dd HH:mm:ss` pattern


