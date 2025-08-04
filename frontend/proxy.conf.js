const target = process.env.PROXY_TARGET || 'http://localhost:8000';

module.exports = [
  {
    context: ['/api'],
    target: target,
    secure: false,

    logLevel: 'debug',
    logProvider: () => console,
    configure: (proxy, _options) => {
      proxy.on("error", (err, _req, _res) => {
        console.log("proxy error", err);
      });
      proxy.on("proxyReq", (proxyReq, req, _res) => {
        const headers = proxyReq.getHeaders();
        console.log(
          req.method,
          req.url,
          " -> ",
          `${headers.host}${proxyReq.path}`,
        );
      });
      proxy.on("proxyRes", (proxyRes, req, _res) => {
        console.log(
          req.method,
          "Target Response",
          proxyRes.statusCode,
          ":",
          req.url,
        );
      });
    }
  },
];
