import sys

file_path = r"c:\laragon\www\helpdesk-laravel\resources\views\soporte\tickets\show.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

# Locate the chat block
start_idx = -1
end_idx = -1
for i, line in enumerate(lines):
    if "{{-- Chat de Comunicación con el Usuario --}}" in line:
        start_idx = i
        break

if start_idx != -1:
    # Find the end of the chat block. It's right before the closing </div> of col-lg-8
    for i in range(start_idx, len(lines)):
        if "{{-- Sidebar Lateral: Ficha Informativa del Incidente --}}" in lines[i]:
            # The chat block ends a few lines before this. Let's find the closing div of the card
            # The structure is:
            #             </div>
            #         </div>
            #
            #         {{-- Sidebar Lateral: Ficha Informativa del Incidente --}}
            end_idx = i - 2
            break

if start_idx != -1 and end_idx != -1:
    chat_html = "".join(lines[start_idx:end_idx])
    
    # Remove from original place
    del lines[start_idx:end_idx]

    # Wrap chat_html in floating panel
    # We will modify the class of the outer div from "card card-premium ..." to "chat-floating-panel"
    chat_html = chat_html.replace(
        '<div class="card card-premium border-0 shadow-sm overflow-hidden mb-5">',
        '<div class="chat-floating-panel" id="chatFloatingPanel">\n<div class="d-flex justify-content-between align-items-center p-3 bg-primary text-white">\n<h6 class="mb-0 fw-bold"><i class="bi bi-chat-dots-fill me-2"></i> Chat del Ticket</h6>\n<button type="button" class="btn-close btn-close-white" id="closeChatBtn"></button>\n</div>'
    )
    # The header inside the original chat was:
    # <div class="card-header bg-secondary bg-opacity-10 py-3 d-flex justify-content-between align-items-center">...</div>
    # Let's just remove that old header since we added a new one.
    import re
    chat_html = re.sub(r'<div class="card-header.*?</div>', '', chat_html, flags=re.DOTALL | re.MULTILINE)

    floating_chat = f"""
<!-- Botón Flotante (A7) -->
<button class="chat-fab" id="openChatBtn" title="Abrir Chat" data-bs-toggle="tooltip" data-bs-placement="left">
    <i class="bi bi-chat-text-fill"></i>
</button>

<!-- Fondo oscurecido -->
<div class="chat-backdrop" id="chatBackdrop"></div>

<!-- Panel Flotante -->
{chat_html}
"""

    # Insert before Modales para Gestores (or at the bottom before @endsection)
    insert_idx = -1
    for i, line in enumerate(lines):
        if "{{-- Modales para Gestores --}}" in line:
            insert_idx = i
            break
    
    if insert_idx == -1:
        for i, line in enumerate(lines):
            if "@endsection" in line:
                insert_idx = i
                break
                
    if insert_idx != -1:
        lines.insert(insert_idx, floating_chat)

    # Now let's update CSS
    css_to_add = """
    .chat-floating-panel {
        position: fixed;
        right: 0;
        top: 0;
        width: 400px;
        height: 100vh;
        background: var(--bs-body-bg);
        border-left: 1px solid var(--bs-border-color);
        box-shadow: -8px 0 30px rgba(0, 0, 0, 0.12);
        z-index: 1050;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .chat-floating-panel.open {
        transform: translateX(0);
    }
    .chat-fab {
        position: fixed;
        bottom: 28px;
        right: 28px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd, #4f46e5);
        color: white;
        border: none;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        z-index: 1040;
        font-size: 1.4rem;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chat-fab:hover {
        transform: scale(1.08);
        box-shadow: 0 8px 28px rgba(13, 110, 253, 0.55);
    }
    .chat-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1045;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .chat-backdrop.show {
        opacity: 1;
        visibility: visible;
    }
    @media (max-width: 575.98px) {
        .chat-floating-panel { width: 100%; }
    }
"""
    # Find .ticket-chat-shell to modify it and add new CSS
    for i, line in enumerate(lines):
        if ".ticket-chat-shell {" in line:
            # We want to replace height: 520px with flex-grow: 1; and insert the new css above it
            lines[i] = css_to_add + "\n" + lines[i]
            break
            
    for i, line in enumerate(lines):
        if "height: 520px;" in line:
            lines[i] = line.replace("height: 520px;", "flex-grow: 1;")
            break
            
    # Modify the JS for scroll to include smooth and toggle logic
    js_old = """document.addEventListener("DOMContentLoaded", function () {
    const chatShell = document.getElementById("ticket-chat-shell");
    if (chatShell) {
        chatShell.scrollTop = chatShell.scrollHeight;
    }
});"""
    js_new = """document.addEventListener("DOMContentLoaded", function () {
    const chatShell = document.getElementById("ticket-chat-shell");
    const openBtn = document.getElementById("openChatBtn");
    const closeBtn = document.getElementById("closeChatBtn");
    const panel = document.getElementById("chatFloatingPanel");
    const backdrop = document.getElementById("chatBackdrop");

    function scrollToBottom() {
        if (chatShell) {
            chatShell.scrollTo({
                top: chatShell.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    function toggleChat() {
        panel.classList.toggle("open");
        backdrop.classList.toggle("show");
        if(panel.classList.contains("open")) {
            setTimeout(scrollToBottom, 300);
        }
    }

    if (openBtn) openBtn.addEventListener("click", toggleChat);
    if (closeBtn) closeBtn.addEventListener("click", toggleChat);
    if (backdrop) backdrop.addEventListener("click", toggleChat);
    
    // Auto-scroll en carga inicial
    scrollToBottom();
});"""
    
    content = "".join(lines)
    content = content.replace(js_old, js_new)

    with open(file_path, "w", encoding="utf-8") as f:
        f.write(content)
    
    print("Chat refactored successfully.")
else:
    print("Could not find chat block bounds.")
