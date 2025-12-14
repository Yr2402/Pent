</main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Projects Components PTY. Soluciones Tecnológicas Integrales.</p>
    </footer>

    <div id="chat-btn" onclick="toggleChat()" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #2563eb; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 30px; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 999999;">
        💬
    </div>

    <div id="chat-window" 
        style="position: fixed; bottom: 100px; right: 30px; width: 320px; height: 450px; background: white; border-radius: 12px; box-shadow: 0 5px 30px rgba(0,0,0,0.2); z-index: 999999; border: 1px solid #ddd; display: none; flex-direction: column; overflow: hidden;">
        
        <div style="background: #2563eb; color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="width: 10px; height: 10px; background: #4ade80; border-radius: 50%;"></span>
                Asistente Virtual
            </div>
            <span style="cursor:pointer; font-size:1.5rem; line-height: 1;" onclick="toggleChat()">×</span>
        </div>
        
        <div id="chat-body" style="flex: 1; padding: 15px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 10px;">
            <div style="background: #e2e8f0; color: #334155; align-self: flex-start; border-radius: 12px; padding: 10px 15px; max-width: 85%; font-size: 0.95rem;">
                👋 ¡Hola! Soy el asistente de Projects PTY.
                <br><br>
                Puedo ayudarte con:
                <br>• <b>Catálogo</b> y Precios
                <br>• <b>Servicios</b> IT
                <br>• <b>Soporte</b> Técnico
            </div>
        </div>
        
        <div style="padding: 15px; border-top: 1px solid #e2e8f0; display: flex; background: white; align-items: center;">
            <input type="text" id="chat-input" placeholder="Escribe tu duda..." onkeypress="if(event.key==='Enter') sendMessage()" 
                    style="flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 20px; outline: none;">
            <button onclick="sendMessage()" 
                    style="background: #2563eb; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; margin-left: 10px; cursor: pointer; font-size: 1.2rem;">➤</button>
        </div>
    </div>

    <script>
        function toggleChat() {
            var chat = document.getElementById("chat-window");
            var btn = document.getElementById("chat-btn");
            
            if (chat.style.display === "none" || chat.style.display === "") {
                chat.style.display = "flex";
                btn.style.display = "none";
                document.getElementById("chat-input").focus();
            } else {
                chat.style.display = "none";
                btn.style.display = "flex";
            }
        }

        function sendMessage() {
            var input = document.getElementById("chat-input");
            var msg = input.value.trim();
            if (msg === "") return;

            addMessage(msg, 'user');
            input.value = "";

            // Simulamos que el bot "piensa" un poco más
            setTimeout(function() { botReply(msg); }, 600);
        }

        function addMessage(text, sender) {
            var body = document.getElementById("chat-body");
            var div = document.createElement("div");
            
            // Permitimos HTML básico en las respuestas del bot (negritas, links)
            div.innerHTML = text; 
            
            div.style.padding = '10px 15px';
            div.style.borderRadius = '12px';
            div.style.maxWidth = '85%';
            div.style.fontSize = '0.95rem';
            div.style.marginBottom = '5px';
            div.style.lineHeight = '1.4';
            
            if (sender === 'user') {
                div.style.background = '#2563eb';
                div.style.color = 'white';
                div.style.alignSelf = 'flex-end';
            } else {
                div.style.background = '#e2e8f0';
                div.style.color = '#334155';
                div.style.alignSelf = 'flex-start';
            }
            body.appendChild(div);
            body.scrollTop = body.scrollHeight;
        }

        // --- LÓGICA DEL BOT CORREGIDA ---
        function botReply(userMsg) {
            // 1. Limpiamos el texto: quitamos acentos y pasamos a minúsculas
            var cleanMsg = userMsg.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            
            var reply = "🤔 No estoy seguro de haberte entendido. ¿Podrías intentar preguntar por <b>precios</b>, <b>soporte</b> o <b>ubicación</b>?";

            // Lógica de palabras clave expandida
            if (cleanMsg.includes("hola") || cleanMsg.includes("buen") || cleanMsg.includes("saludo")) {
                reply = "¡Hola! 👋 ¿En qué te puedo ayudar hoy? Te recomiendo ver nuestro <a href='/views/productos.php' style='color:#2563eb'>Catálogo de Hardware</a>.";
            }
            
            // **CORRECCIÓN AQUÍ: Se añade el enlace al Catálogo de Hardware**
            else if (cleanMsg.includes("precio") || cleanMsg.includes("precios") || cleanMsg.includes("costo") || cleanMsg.includes("costos") || cleanMsg.includes("cuanto vale") || cleanMsg.includes("cuanto cuesta") || cleanMsg.includes("valor") || cleanMsg.includes("tarifa") || cleanMsg.includes("catalogo") || cleanMsg.includes("catálogo") || cleanMsg.includes("producto") || cleanMsg.includes("productos") || cleanMsg.includes("venta") || cleanMsg.includes("ventas") || cleanMsg.includes("comprar") || cleanMsg.includes("compra") || cleanMsg.includes("cotizacion") || cleanMsg.includes("cotización") || cleanMsg.includes("oferta"))

 {
                reply = "💰 Tenemos excelentes precios en servidores y equipos. Puedes ver el inventario completo y precios en nuestro <a href='/views/productos.php' style='color:#2563eb'>Catálogo de Hardware</a>.";
            }
            
            // Soporte / Ayuda / Error
            else if (cleanMsg.includes("soporte") || cleanMsg.includes("ayuda") || cleanMsg.includes("error") || cleanMsg.includes("problema") || cleanMsg.includes("ticket") || cleanMsg.includes("tecnico"))
 {
                reply = "👨‍🔧 Para asistencia técnica, por favor abre un caso en nuestra sección de <a href='/views/soporte.php' style='color:#2563eb'>Soporte</a>. Un técnico te atenderá.";
            }
            // Servicios
            else if (cleanMsg.includes("servicio") || cleanMsg.includes("servicios") || cleanMsg.includes("hacen") || cleanMsg.includes("ofrecen") || cleanMsg.includes("soporte") || cleanMsg.includes("mantenimiento") || cleanMsg.includes("cloud") || cleanMsg.includes("nube") || cleanMsg.includes("hosting") || cleanMsg.includes("seguridad") || cleanMsg.includes("ciberseguridad") || cleanMsg.includes("it"))

 {
                reply = "🚀 Ofrecemos Cloud Hosting, Ciberseguridad y Mantenimiento IT. ¿Deseas <a href='/views/solicitud.php' style='color:#2563eb'>solicitar una cotización</a>?";
            }
            // Ubicación / Contacto
            else if (cleanMsg.includes("ubicacion") || cleanMsg.includes("donde") || cleanMsg.includes("direccion") || cleanMsg.includes("telefono") || cleanMsg.includes("contacto")) {
                reply = "📍 Operamos 100% online desde Ciudad de Panamá. Contáctanos vía Soporte para agendar una visita.";
            }
            // Despedida
            else if (cleanMsg.includes("gracias") || cleanMsg.includes("adios") || cleanMsg.includes("chao")) {
                reply = "¡De nada! Estamos para servirte. Que tengas un excelente día. 😊";
            }

            addMessage(reply, 'bot');
        }
    </script>
</body>
</html>