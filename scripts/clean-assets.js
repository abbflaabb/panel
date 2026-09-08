const fs = require('fs');
const path = require('path');

const assetsDirectory = path.resolve(__dirname, '..', 'public', 'assets');
const removableExtensions = new Set(['.js', '.map']);

if (!fs.existsSync(assetsDirectory)) {
    process.exit(0);
}

const removeGeneratedAssets = (directory) => {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        const entryPath = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            removeGeneratedAssets(entryPath);
            continue;
        }

        if (removableExtensions.has(path.extname(entry.name).toLowerCase())) {
            fs.rmSync(entryPath, { force: true });
        }
    }
};

removeGeneratedAssets(assetsDirectory);
