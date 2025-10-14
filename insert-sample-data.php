<?php
/**
 * Script to insert sample tender data into the database
 */

// Change to the Drupal root
chdir('/var/www/html');

// Load sample data SQL file
$sampleDataFile = '/var/www/html/sample-tender-data.sql';

if (!file_exists($sampleDataFile)) {
    echo "❌ Sample data file not found!\n";
    echo "Please make sure sample-tender-data.sql is copied to the container.\n";
    exit(1);
}

// Database connection parameters
$host = 'db';
$port = 5432;
$dbname = 'tender_management';
$user = 'drupal';
$password = 'drupal123';

try {
    // Connect to PostgreSQL database
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔗 Connected to database successfully!\n\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents($sampleDataFile);
    
    // Split SQL file into individual statements
    $statements = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    echo "📝 Executing SQL statements...\n\n";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements and comments
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Show progress for important operations
            if (strpos($statement, 'INSERT INTO') === 0) {
                preg_match('/INSERT INTO (\w+)/', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✅ Inserted data into: {$matches[1]}\n";
                }
            }
            
        } catch (PDOException $e) {
            $errorCount++;
            echo "❌ Error executing statement: " . $e->getMessage() . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n\n";
        }
    }
    
    echo "\n📊 SAMPLE DATA INSERTION SUMMARY:\n";
    echo "✅ Successful operations: $successCount\n";
    echo "❌ Failed operations: $errorCount\n\n";
    
    // Show final data counts
    echo "📈 DATA VERIFICATION:\n";
    
    $tables = [
        'content_categories' => 'Content Categories',
        'content_procurements' => 'Content Procurements (Tenders)',
        'content_producers' => 'Content Producers (Companies)',
        'content_proposals' => 'Content Proposals (Bids)',
        'evaluation_criteria' => 'Evaluation Criteria',
        'evaluation_scores' => 'Evaluation Scores',
        'content_workflow' => 'Workflow Entries',
        'notifications' => 'Notifications',
        'procurement_documents' => 'Documents'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "📋 $description: $count records\n";
        } catch (PDOException $e) {
            echo "❌ Error counting $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 SAMPLE DATA READY!\n";
    echo "Your tender management system now includes:\n";
    echo "• 8 TV Content Categories (Swasta Baharu, Sambung Siri, etc.)\n";
    echo "• 5 Active Content Procurements (Drama, Documentary, etc.)\n";
    echo "• 5 Registered Content Producers (Production Companies)\n";
    echo "• 5 Content Proposals (Bids from producers)\n";
    echo "• Complete evaluation criteria and scoring\n";
    echo "• Workflow tracking and notifications\n";
    echo "• Document management examples\n\n";
    
    echo "🌐 You can now:\n";
    echo "• View tenders at: /tender-management\n";
    echo "• Check procurement status and proposals\n";
    echo "• Review evaluation scores and workflow\n";
    echo "• Test the complete tender lifecycle\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Make sure the database is running and accessible.\n";
    exit(1);
}