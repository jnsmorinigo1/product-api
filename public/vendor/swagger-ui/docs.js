// Swagger UI 5.34.0 is saved here, so the docs page does not need internet.
// validatorUrl is null, so the browser does not send the file to validator.swagger.io.
const root = document.getElementById('swagger-ui');

window.ui = SwaggerUIBundle({
    url: root.dataset.specUrl,
    dom_id: '#swagger-ui',
    persistAuthorization: false,
    validatorUrl: null,
});
