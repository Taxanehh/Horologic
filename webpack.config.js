switch (process.env.ENV) {
    case 'live':
    case 'prod':
    case 'production':
        module.exports = require('./webpack/webpack.prod');
        break;

    case 'local':
    case 'dev':
    case 'development':
    default:
        module.exports = require('./webpack/webpack.dev');
}
