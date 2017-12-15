module.exports = {
    entry: './assets/import.js',
    output: {
        filename: './assets/build/import.js',
    },
    node: {
        console: true,
        fs: 'empty',
        net: 'empty',
        tls: true
    }
};