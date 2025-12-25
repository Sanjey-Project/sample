$mappingFile = "c:\xampp\htdocs\academic-performance-analysis-es\elasticsearch_mappings_numeric.json"
$baseUrl = "http://localhost:9200"
$ProgressPreference = 'SilentlyContinue'

if (-not (Test-Path $mappingFile)) {
    Write-Error "Mapping file not found at $mappingFile"
    exit 1
}

$jsonContent = Get-Content -Path $mappingFile -Raw | ConvertFrom-Json
$indicesToCreate = $jsonContent.PSObject.Properties.Name

foreach ($indexName in $indicesToCreate) {
    if ($jsonContent.PSObject.Properties.Match($indexName).Count -eq 0) {
        Write-Warning "Index mapping for '$indexName' not found in JSON."
        continue
    }

    $indexData = $jsonContent.$indexName
    
    # Define custom settings for "case_insensitive" normalizer which is missing
    $settings = @{
        "analysis" = @{
            "normalizer" = @{
                "case_insensitive" = @{
                    "type"   = "custom"
                    "filter" = @("lowercase")
                }
            }
        }
    }

    # Construct complete body with settings and mappings
    # $indexData already has "mappings" key
    $fullBody = @{
        "settings" = $settings
        "mappings" = $indexData.mappings
    }

    $body = $fullBody | ConvertTo-Json -Depth 10
    
    $url = "$baseUrl/$indexName"
    Write-Host "Checking index '$indexName' at $url..."

    try {
        $exists = Invoke-WebRequest -Uri $url -Method Head -UseBasicParsing -ErrorAction SilentlyContinue
        if ($exists.StatusCode -eq 200) {
            Write-Warning "Index '$indexName' already exists. Skipping..."
            continue
        }
    }
    catch {
        # Ignore 404
    }

    Write-Host "Creating index '$indexName'..."
    try {
        $response = Invoke-WebRequest -Uri $url -Method Put -Body $body -ContentType "application/json" -UseBasicParsing
        Write-Host "Successfully created index '$indexName'. Status: $($response.StatusCode)"
    }
    catch {
        Write-Error "Failed to create index '$indexName'."
        if ($_.Exception.Response) {
            # Read error stream
            $stream = $_.Exception.Response.GetResponseStream()
            if ($stream) {
                $reader = New-Object System.IO.StreamReader($stream)
                Write-Error $reader.ReadToEnd()
            }
        }
        else {
            Write-Error $_.Exception.Message
        }
    }
}
