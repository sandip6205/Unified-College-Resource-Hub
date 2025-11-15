// AI Chatbot Widget
class CollegeChatBot {
    constructor() {
        this.isOpen = false;
        this.sessionId = this.generateSessionId();
        this.messages = [];
        this.isTyping = false;
        
        this.init();
    }
    
    generateSessionId() {
        return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    init() {
        this.createChatWidget();
        this.bindEvents();
    }
    
    createChatWidget() {
        const chatHTML = `
            <!-- Chat Button -->
            <div id="chatbot-button" class="fixed bottom-6 right-6 z-50">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 transform hover:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </button>
                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse" id="chat-notification" style="display: none;">
                    !
                </div>
            </div>
            
            <!-- Chat Window -->
            <div id="chatbot-window" class="fixed bottom-24 right-6 w-80 h-96 bg-white rounded-lg shadow-2xl z-50 transform transition-all duration-300 scale-0 origin-bottom-right">
                <!-- Header -->
                <div class="bg-indigo-600 text-white p-4 rounded-t-lg flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                            🤖
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm">College Assistant</h3>
                            <p class="text-xs opacity-75">Online</p>
                        </div>
                    </div>
                    <button id="chat-close" class="text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Messages -->
                <div id="chat-messages" class="h-64 overflow-y-auto p-4 space-y-3">
                    <div class="flex items-start space-x-2">
                        <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            🤖
                        </div>
                        <div class="bg-gray-100 rounded-lg p-2 max-w-xs">
                            <p class="text-sm">Hello! 👋 I'm your College Resource Hub assistant. How can I help you today?</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Replies -->
                <div id="quick-replies" class="px-4 pb-2">
                    <div class="flex flex-wrap gap-1">
                        <button class="quick-reply-btn bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full hover:bg-indigo-200" data-message="Find Resources">📚 Find Resources</button>
                        <button class="quick-reply-btn bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full hover:bg-indigo-200" data-message="Upload Help">📤 Upload Help</button>
                        <button class="quick-reply-btn bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full hover:bg-indigo-200" data-message="Login Help">🔐 Login Help</button>
                    </div>
                </div>
                
                <!-- Input -->
                <div class="border-t p-4">
                    <div class="flex space-x-2">
                        <input type="text" id="chat-input" placeholder="Type your message..." 
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button id="chat-send" class="bg-indigo-600 text-white rounded-lg px-4 py-2 hover:bg-indigo-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatHTML);
    }
    
    bindEvents() {
        const chatButton = document.getElementById('chatbot-button');
        const chatWindow = document.getElementById('chatbot-window');
        const chatClose = document.getElementById('chat-close');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        
        // Toggle chat window
        chatButton.addEventListener('click', () => this.toggleChat());
        chatClose.addEventListener('click', () => this.closeChat());
        
        // Send message
        chatSend.addEventListener('click', () => this.sendMessage());
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
        
        // Quick replies
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-reply-btn')) {
                const message = e.target.getAttribute('data-message');
                this.sendMessage(message);
            }
        });
    }
    
    toggleChat() {
        const chatWindow = document.getElementById('chatbot-window');
        
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }
    
    openChat() {
        const chatWindow = document.getElementById('chatbot-window');
        const notification = document.getElementById('chat-notification');
        
        chatWindow.classList.remove('scale-0');
        chatWindow.classList.add('scale-100');
        notification.style.display = 'none';
        
        this.isOpen = true;
        
        // Focus input
        setTimeout(() => {
            document.getElementById('chat-input').focus();
        }, 300);
    }
    
    closeChat() {
        const chatWindow = document.getElementById('chatbot-window');
        
        chatWindow.classList.remove('scale-100');
        chatWindow.classList.add('scale-0');
        
        this.isOpen = false;
    }
    
    async sendMessage(message = null) {
        const chatInput = document.getElementById('chat-input');
        const messageText = message || chatInput.value.trim();
        
        if (!messageText) return;
        
        // Clear input
        if (!message) {
            chatInput.value = '';
        }
        
        // Add user message to chat
        this.addMessage(messageText, 'user');
        
        // Show typing indicator
        this.showTyping();
        
        try {
            // Send to API - determine correct path
            const currentPath = window.location.pathname;
            let apiPath = 'api/simple_chatbot.php';
            if (currentPath.includes('/dashboard/')) {
                apiPath = '../api/simple_chatbot.php';
            }
            
            const response = await fetch(apiPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: messageText,
                    session_id: this.sessionId
                })
            });
            
            const data = await response.json();
            
            // Hide typing indicator
            this.hideTyping();
            
            if (data.success) {
                // Add bot response
                this.addMessage(data.response.message, 'bot');
                
                // Update quick replies
                if (data.response.metadata && data.response.metadata.quick_replies) {
                    this.updateQuickReplies(data.response.metadata.quick_replies);
                }
            } else {
                this.addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
            
        } catch (error) {
            this.hideTyping();
            this.addMessage('Sorry, I\'m having trouble connecting. Please check your internet connection.', 'bot');
        }
    }
    
    addMessage(message, sender) {
        const messagesContainer = document.getElementById('chat-messages');
        const isBot = sender === 'bot';
        
        const messageHTML = `
            <div class="flex items-start space-x-2 ${isBot ? '' : 'flex-row-reverse space-x-reverse'}">
                <div class="w-6 h-6 ${isBot ? 'bg-indigo-100' : 'bg-blue-100'} rounded-full flex items-center justify-center flex-shrink-0">
                    ${isBot ? '🤖' : '👤'}
                </div>
                <div class="${isBot ? 'bg-gray-100' : 'bg-blue-500 text-white'} rounded-lg p-2 max-w-xs">
                    <p class="text-sm whitespace-pre-line">${this.escapeHtml(message)}</p>
                </div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    showTyping() {
        const messagesContainer = document.getElementById('chat-messages');
        
        const typingHTML = `
            <div id="typing-indicator" class="flex items-start space-x-2">
                <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                    🤖
                </div>
                <div class="bg-gray-100 rounded-lg p-2">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    hideTyping() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    updateQuickReplies(replies) {
        const quickRepliesContainer = document.getElementById('quick-replies');
        
        const repliesHTML = replies.map(reply => 
            `<button class="quick-reply-btn bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full hover:bg-indigo-200" data-message="${reply}">${reply}</button>`
        ).join('');
        
        quickRepliesContainer.innerHTML = `<div class="flex flex-wrap gap-1">${repliesHTML}</div>`;
    }
    
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if not already initialized
    if (!window.collegeChatBot) {
        window.collegeChatBot = new CollegeChatBot();
    }
});
