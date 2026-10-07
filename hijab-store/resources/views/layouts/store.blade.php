<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hijab Store')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fbf9f7; color: #302b29; }
        .navbar, .card { border-color: #eee5df; }
        .brand { color: #704c43; font-weight: 700; letter-spacing: .04em; }
        .btn-primary { background: #845e53; border-color: #845e53; }
        .btn-primary:hover { background: #68473e; border-color: #68473e; }
        .product-image { height: 220px; object-fit: cover; background: #f1e9e3; }
        .product-placeholder { height: 220px; display: grid; place-items: center; background: #f1e9e3; color: #8a7167; }
        footer { color: #756963; }

        .chatbot-toggle {
            position: fixed;
            right: 24px;
            bottom: 24px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 12px 28px rgba(132, 94, 83, 0.25);
            z-index: 1050;
        }

        .chatbot-panel {
            position: fixed;
            right: 24px;
            bottom: 96px;
            width: min(360px, calc(100vw - 24px));
            max-height: 480px;
            background: #fff;
            border: 1px solid #f1e9e3;
            border-radius: 20px;
            box-shadow: 0 18px 36px rgba(48, 43, 41, 0.12);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1040;
        }

        .chatbot-panel.is-open {
            display: flex;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #845e53, #b18374);
            color: #fff;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
        }

        .chatbot-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px;
            background: #fffaf7;
            overflow-y: auto;
        }

        .chat-message {
            max-width: 86%;
            padding: 10px 12px;
            border-radius: 14px;
            line-height: 1.5;
            font-size: 0.92rem;
        }

        .chat-message.user {
            background: #f0e5e1;
            color: #2f2826;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .chat-message.assistant {
            background: #fff;
            border: 1px solid #f1e9e3;
            color: #403634;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .chat-recommendations {
            display: grid;
            gap: 8px;
            margin-top: 8px;
        }

        .chat-recommendation {
            background: #fff;
            border: 1px solid #f1e9e3;
            border-radius: 12px;
            padding: 8px 10px;
        }

        .chat-recommendation a {
            text-decoration: none;
            color: #5f433e;
            font-weight: 600;
        }

        .chat-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 14px 10px;
            background: #fffaf7;
        }

        .chat-quick-btn {
            border: 1px solid #e8d9d2;
            background: #fff;
            color: #473a36;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .chat-form {
            display: flex;
            gap: 8px;
            padding: 10px 14px 14px;
            border-top: 1px solid #f3e8e4;
            background: #fff;
        }

        .chat-form input {
            flex: 1;
            border-radius: 999px;
            border: 1px solid #e6d9d1;
            padding: 10px 12px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand brand" href="{{ route('home') }}">HIJAB STORE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#storeNav" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="storeNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="{{ route('home') }}">Katalog</a>
                <a class="nav-link" href="{{ route('products.search') }}">Cari produk</a>
            </div>
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <a class="btn btn-outline-secondary" href="{{ route('cart.index') }}">Keranjang</a>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a class="btn btn-dark" href="{{ route('admin.dashboard') }}">Panel admin</a>
                        <form method="post" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary">Keluar</button>
                        </form>
                    @else
                        <a class="btn btn-dark" href="{{ route('account.show') }}">Akun saya</a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary">Keluar</button>
                        </form>
                    @endif
                @else
                    <a class="btn btn-outline-secondary" href="{{ route('login') }}">Masuk</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Daftar</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
<main class="container py-4">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
<footer class="container py-4 border-top small">Hijab Store · Belanja hijab dengan nyaman</footer>

<div class="chatbot-widget">
    <button id="chatbotToggle" class="btn btn-primary chatbot-toggle" type="button" aria-label="Buka asisten Hijab Store">💬</button>

    <div id="chatbotPanel" class="chatbot-panel" aria-live="polite">
        <div class="chatbot-header">
            <span>Asisten Hijab Store</span>
            <button class="btn-close btn-close-white" id="chatbotClose" type="button" aria-label="Tutup chat"></button>
        </div>

        <div class="chatbot-body" id="chatbotMessages">
            <div class="chat-message assistant">Halo! Mau cari hijab yang paling cocok untuk kebutuhanmu? Coba tanyakan model, bahan, atau acara.</div>
        </div>

        <div class="chat-quick-actions">
            <button class="chat-quick-btn" type="button" data-prompt="mau hijab formal untuk acara penting">Formal</button>
            <button class="chat-quick-btn" type="button" data-prompt="butuh hijab bahan adem untuk harian">Adem</button>
            <button class="chat-quick-btn" type="button" data-prompt="rekomendasi hijab motif cantik">Motif</button>
        </div>

        <form id="chatbotForm" class="chat-form">
            <input id="chatbotInput" type="text" placeholder="Tulis kebutuhanmu..." aria-label="Pesan chatbot">
            <button class="btn btn-primary" type="submit">Kirim</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotPanel = document.getElementById('chatbotPanel');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function appendMessage(role, text) {
        const item = document.createElement('div');
        item.className = `chat-message ${role}`;
        item.textContent = text;
        chatbotMessages.appendChild(item);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function appendRecommendations(items) {
        const wrapper = document.createElement('div');
        wrapper.className = 'chat-recommendations';

        items.forEach((item) => {
            const box = document.createElement('div');
            box.className = 'chat-recommendation';
            box.innerHTML = '<a href="' + item.url + '">' + item.name + ' · Rp ' + Number(item.price).toLocaleString('id-ID') + '</a>';
            wrapper.appendChild(box);
        });

        chatbotMessages.appendChild(wrapper);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function toggleChatbot(forceOpen) {
        const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !chatbotPanel.classList.contains('is-open');
        chatbotPanel.classList.toggle('is-open', shouldOpen);
    }

    chatbotToggle.addEventListener('click', () => toggleChatbot());
    chatbotClose.addEventListener('click', () => toggleChatbot(false));

    document.querySelectorAll('[data-prompt]').forEach((button) => {
        button.addEventListener('click', () => {
            chatbotInput.value = button.dataset.prompt;
            chatbotInput.focus();
            toggleChatbot(true);
        });
    });

    chatbotForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const message = chatbotInput.value.trim();
        if (!message) {
            return;
        }

        appendMessage('user', message);
        chatbotInput.value = '';
        chatbotInput.disabled = true;

        try {
            const response = await fetch('/chatbot/ask', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ message }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.reply || data.message || 'Gagal memproses permintaan');
            }

            appendMessage('assistant', data.reply || 'Saya siap membantu.');

            if (data.recommendations && data.recommendations.length) {
                appendRecommendations(data.recommendations);
            }
        } catch (error) {
            appendMessage('assistant', error.message || 'Maaf, saya sedang tidak bisa membantu saat ini.');
        } finally {
            chatbotInput.disabled = false;
            chatbotInput.focus();
        }
    });
</script>
</body>
</html>
