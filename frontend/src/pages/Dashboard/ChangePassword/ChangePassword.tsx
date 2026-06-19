import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { SEOHead } from '../../../components/common/SEOHead';
import { toast } from 'react-toastify';
import api from '../../../services/api';

const ChangePassword: React.FC = () => {
  const navigate = useNavigate();
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!currentPassword || !newPassword || !confirmPassword) {
      toast.error('Por favor, preencha todos os campos.');
      return;
    }

    if (newPassword !== confirmPassword) {
      toast.error('As senhas não coincidem.');
      return;
    }

    if (newPassword.length < 8) {
      toast.error('A nova senha deve ter pelo menos 8 caracteres.');
      return;
    }

    try {
      setLoading(true);
      await api.put('/admin/change-password', {
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword,
      });

      toast.success('Senha alterada com sucesso!');
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
      navigate('/admin');
    } catch (error: any) {
      console.error('Erro ao alterar senha', error);
      toast.error(error.response?.data?.message || 'Erro ao alterar senha.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-sm p-6">
      <SEOHead title="Alterar Senha — VendaPop" noIndex />
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Alterar Senha</h1>
        <p className="mt-1 text-sm text-gray-500">
          Altere sua senha de acesso ao painel administrativo
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6 max-w-md">
        <div>
          <label htmlFor="current_password" className="block text-sm font-medium text-gray-700 mb-2">
            Senha Atual
          </label>
          <input
            type="password"
            id="current_password"
            value={currentPassword}
            onChange={(e) => setCurrentPassword(e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            required
            placeholder="Digite sua senha atual"
          />
        </div>

        <div>
          <label htmlFor="new_password" className="block text-sm font-medium text-gray-700 mb-2">
            Nova Senha
          </label>
          <input
            type="password"
            id="new_password"
            value={newPassword}
            onChange={(e) => setNewPassword(e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            required
            minLength={8}
            placeholder="Digite sua nova senha (mínimo 8 caracteres)"
          />
          <p className="mt-1 text-xs text-gray-500">
            Mínimo de 8 caracteres
          </p>
        </div>

        <div>
          <label htmlFor="confirm_password" className="block text-sm font-medium text-gray-700 mb-2">
            Confirmar Nova Senha
          </label>
          <input
            type="password"
            id="confirm_password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            className="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            required
            minLength={8}
            placeholder="Confirme sua nova senha"
          />
        </div>

        <div className="flex gap-3">
          <button
            type="submit"
            disabled={loading}
            className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {loading ? 'Alterando...' : 'Alterar Senha'}
          </button>
          <button
            type="button"
            onClick={() => navigate('/admin')}
            className="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
          >
            Cancelar
          </button>
        </div>
      </form>
    </div>
  );
};

export default ChangePassword;

