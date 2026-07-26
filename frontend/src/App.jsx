import React, { useState, useEffect, useRef } from 'react';
import './App.css';

function App() {
  const [messages, setMessages] = useState([
    { id: 1, text: "Olá! Sou o atendimento da Alves. Como posso te ajudar hoje com produtos ou informações?", sender: ' ' }
  ]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const scrollRef = useRef(null);

  useEffect(() => {
    scrollRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const sendMessage = async (e) => {
    e.preventDefault();
    if (!input.trim() || loading) return;

    const userText = input;
    setMessages(prev => [...prev, { id: Date.now(), text: userText, sender: 'user' }]);
    setInput('');
    setLoading(true);

    try {
      const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/chat';

      const response = await fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mensagem: userText })
      });

      if (!response.ok) throw new Error("Erro na requisição");

      const data = await response.json();


      const botText = data.resposta || data.error || "O servidor não enviou uma resposta válida.";

      setMessages(prev => [...prev, { id: Date.now() + 1, text: botText, sender: 'bot' }]);
    } catch (error) {
      console.error(error);
      setMessages(prev => [...prev, { id: Date.now() + 1, text: "Erro ao conectar com o backend. Verifique se o Apache está ligado no XAMPP.", sender: 'bot', error: true }]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="chat-container">
      <div className="chat-header"><h2>Atendimento Alves</h2></div>
      <div className="chat-window">
        {messages.map((m) => (
          <div key={m.id} className={`message-bubble ${m.sender} ${m.error ? 'error' : ''}`}>
            {m.text}
          </div>
        ))}
        {loading && <div className="loading">Pensando...</div>}
        <div ref={scrollRef} />
      </div>
      <form className="chat-input" onSubmit={sendMessage}>
        <input value={input} onChange={(e) => setInput(e.target.value)} placeholder="Pergunte algo..." />
        <button type="submit" disabled={loading}>Enviar</button>
      </form>
    </div>
  );
}

export default App;