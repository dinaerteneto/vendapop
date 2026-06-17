import React from 'react';
import { useState } from 'react';
import api from '../services/api';

const FeedbackWidget: React.FC = () => {
  const [open, setOpen] = useState(false);
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [sent, setSent] = useState(false);
  const [sending, setSending] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSending(true);
    try {
      await api.post('/admin/feedback', { subject, message });
      setSent(true);
      setTimeout(() => { setOpen(false); setSent(false); setSubject(''); setMessage(''); }, 2000);
    } catch (err) {
      console.error('Erro ao enviar feedback', err);
    } finally {
      setSending(false);
    }
  };

  return (
    <>
      <button
        onClick={() => setOpen(!open)}
        className="fixed bottom-6 right-6 z-50 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition-colors flex items-center justify-center text-2xl"
        title="Enviar feedback"
      >
        💬
      </button>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-800">Enviar Feedback</h3>
              <button onClick={() => setOpen(false)} className="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            {sent ? (
              <div className="text-center py-6 text-green-600 font-medium">Obrigado pelo feedback!</div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Assunto</label>
                  <input type="text" value={subject} onChange={e => setSubject(e.target.value)}
                    className="w-full px-3 py-2 border rounded" required placeholder="Sugestão, bug, dúvida..." />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
                  <textarea value={message} onChange={e => setMessage(e.target.value)}
                    className="w-full px-3 py-2 border rounded" rows={4} required placeholder="Descreva seu feedback..." />
                </div>
                <button type="submit" disabled={sending}
                  className="w-full py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">
                  {sending ? 'Enviando...' : 'Enviar'}
                </button>
              </form>
            )}
          </div>
        </div>
      )}
    </>
  );
};

export default FeedbackWidget;
