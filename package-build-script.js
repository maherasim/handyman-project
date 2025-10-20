#!/usr/bin/env node

/**
 * Enhanced Build Script for Vue/Laravel Project
 * Addresses build inconsistencies and conflicts
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

class BuildManager {
    constructor() {
        this.projectRoot = process.cwd();
        this.buildLog = [];
    }

    log(message) {
        const timestamp = new Date().toISOString();
        console.log(`[${timestamp}] ${message}`);
        this.buildLog.push(`[${timestamp}] ${message}`);
    }

    error(message) {
        const timestamp = new Date().toISOString();
        console.error(`[${timestamp}] ERROR: ${message}`);
        this.buildLog.push(`[${timestamp}] ERROR: ${message}`);
    }

    async cleanBuild() {
        this.log('Cleaning previous build artifacts...');
        
        const cleanPaths = [
            'public/js/*.js',
            'public/js/*.txt',
            'public/css/*.css',
            'public/mix-manifest.json'
        ];

        for (const cleanPath of cleanPaths) {
            try {
                execSync(`rm -rf ${cleanPath}`, { stdio: 'inherit' });
            } catch (error) {
                // Ignore errors for non-existent files
            }
        }

        this.log('Build artifacts cleaned successfully');
    }

    async installDependencies() {
        this.log('Installing/updating dependencies...');
        
        try {
            execSync('npm install', { stdio: 'inherit' });
            this.log('Dependencies installed successfully');
        } catch (error) {
            this.error('Failed to install dependencies');
            throw error;
        }
    }

    async buildAssets() {
        this.log('Building assets...');
        
        try {
            // Build for production
            execSync('npm run production', { stdio: 'inherit' });
            this.log('Assets built successfully');
        } catch (error) {
            this.error('Failed to build assets');
            throw error;
        }
    }

    async verifyBuild() {
        this.log('Verifying build...');
        
        const requiredFiles = [
            'public/js/backend-bundle.min.js',
            'public/js/landing-app.min.js',
            'public/css/backend-bundle.min.css',
            'public/css/landing-page.min.css',
            'public/mix-manifest.json'
        ];

        const missingFiles = [];
        
        for (const file of requiredFiles) {
            if (!fs.existsSync(file)) {
                missingFiles.push(file);
            }
        }

        if (missingFiles.length > 0) {
            this.error(`Missing build files: ${missingFiles.join(', ')}`);
            throw new Error('Build verification failed');
        }

        this.log('Build verification passed');
    }

    async optimizeBuild() {
        this.log('Optimizing build...');
        
        // Check for duplicate dependencies
        const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
        const dependencies = { ...packageJson.dependencies, ...packageJson.devDependencies };
        
        const duplicateDeps = [];
        const seenDeps = new Set();
        
        for (const [name, version] of Object.entries(dependencies)) {
            if (seenDeps.has(name)) {
                duplicateDeps.push(name);
            }
            seenDeps.add(name);
        }

        if (duplicateDeps.length > 0) {
            this.log(`Found duplicate dependencies: ${duplicateDeps.join(', ')}`);
        }

        // Check for conflicting versions
        const conflictingDeps = [];
        if (dependencies['bootstrap'] && dependencies['bootstrap-vue']) {
            conflictingDeps.push('bootstrap + bootstrap-vue');
        }

        if (conflictingDeps.length > 0) {
            this.log(`Potential conflicts: ${conflictingDeps.join(', ')}`);
        }

        this.log('Build optimization completed');
    }

    async generateBuildReport() {
        this.log('Generating build report...');
        
        const report = {
            timestamp: new Date().toISOString(),
            buildLog: this.buildLog,
            files: {},
            size: {}
        };

        // Check file sizes
        const buildFiles = [
            'public/js/backend-bundle.min.js',
            'public/js/landing-app.min.js',
            'public/css/backend-bundle.min.css',
            'public/css/landing-page.min.css'
        ];

        for (const file of buildFiles) {
            if (fs.existsSync(file)) {
                const stats = fs.statSync(file);
                report.files[file] = {
                    exists: true,
                    size: stats.size,
                    modified: stats.mtime
                };
                report.size[file] = `${(stats.size / 1024 / 1024).toFixed(2)} MB`;
            } else {
                report.files[file] = { exists: false };
            }
        }

        // Save report
        fs.writeFileSync('build-report.json', JSON.stringify(report, null, 2));
        this.log('Build report saved to build-report.json');
    }

    async run() {
        try {
            this.log('Starting enhanced build process...');
            
            await this.cleanBuild();
            await this.installDependencies();
            await this.buildAssets();
            await this.verifyBuild();
            await this.optimizeBuild();
            await this.generateBuildReport();
            
            this.log('Build process completed successfully!');
            console.log('\n✅ Build Summary:');
            console.log('   - Assets compiled successfully');
            console.log('   - Build verification passed');
            console.log('   - Optimization completed');
            console.log('   - Report generated');
            console.log('\n🚀 Your application is ready for deployment!');
            
        } catch (error) {
            this.error(`Build process failed: ${error.message}`);
            console.error('\n❌ Build Summary:');
            console.error('   - Build process failed');
            console.error('   - Check the logs above for details');
            console.error('\n🔧 Troubleshooting:');
            console.error('   1. Check for conflicting dependencies');
            console.error('   2. Verify Node.js and npm versions');
            console.error('   3. Clear npm cache: npm cache clean --force');
            console.error('   4. Delete node_modules and package-lock.json, then npm install');
            process.exit(1);
        }
    }
}

// Run the build manager
if (require.main === module) {
    const buildManager = new BuildManager();
    buildManager.run();
}

module.exports = BuildManager;
