const env = process.env;

require('laravel-echo-server').run({
	appKey: "base64:rYHL0khJVpZZ7j6k41CIPpDvzpRdUQqZnUoj/RNM99Y=",
	authHost: "https://mexico.grainchain.io",
	authEndpoint: "/broadcasting/auth",
	clients: [
		{
			appId: "rYHL0khJVpZZ7j6k41CIPpDvzpRdUQqZnUoj/RNM99Y=",
			key: "5f8f39cbcbf68c58ebb174eb063e5efb"
		}
	],
	database: "redis",
	databaseConfig: {
		redis: {
			host: "localhost",
			port: 6379
		},
		sqlite: {
			databasePath: "/database/laravel-echo-server.sqlite"
		}
	},
	devMode: true,
	host: "mexico.grainchain.io",
	port: "8443",
	protocol: "https",
	socketio: {},
	sslCertPath: "/etc/ssl/certs/certificates.crt",
	sslKeyPath: "/etc/ssl/private/certificates.key",
	sslCertChainPath: "",
	sslPassphrase: "",
	apiOriginAllow: {
		allowCors: true,
		allowOrigin: "*",
		allowMethods: "GET,POST,PUT,DELETE",
		allowHeaders: "Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, X-CSRF-TOKEN, X-Socket-Id, X-Frame-Options"
	}
});
