<?php

/**
 * Script para gerar chaves VAPID para Web Push Notifications
 * 
 * Uso: php scripts/generate-vapid-keys.php
 * 
 * Este script gera um par de chaves VAPID (pública e privada) necessárias
 * para enviar push notifications via Web Push API.
 */

// Verifica se a biblioteca web-push está disponível
if (!class_exists(\Minishlink\WebPush\VAPID::class)) {
    echo "⚠️  Biblioteca web-push não encontrada.\n";
    echo "📦 Instalando biblioteca...\n\n";
    
    // Tenta instalar via composer
    $composerCommand = 'composer require minishlink/web-push';
    exec($composerCommand, $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo "❌ Erro ao instalar biblioteca. Execute manualmente:\n";
        echo "   docker exec moda-backend composer require minishlink/web-push\n\n";
        exit(1);
    }
    
    echo "✅ Biblioteca instalada com sucesso!\n\n";
}

require __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\VAPID;

echo "🔑 Gerando chaves VAPID...\n\n";

// Gera as chaves
$keys = VAPID::createVapidKeys();

echo "✅ Chaves geradas com sucesso!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📋 Adicione estas variáveis ao arquivo .env do backend:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "VAPID_PUBLIC_KEY={$keys['publicKey']}\n";
echo "VAPID_PRIVATE_KEY={$keys['privateKey']}\n";
echo "VAPID_SUBJECT=mailto:admin@vestezap.com.br\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "💡 Dicas:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• VAPID_PUBLIC_KEY: Chave pública (pode ser compartilhada)\n";
echo "• VAPID_PRIVATE_KEY: Chave privada (mantenha em segredo!)\n";
echo "• VAPID_SUBJECT: Email de contato ou URL (formato: mailto:email@exemplo.com)\n";
echo "• Após adicionar ao .env, reinicie o container backend\n";
echo "• Push notifications funcionam apenas em HTTPS (ou localhost para testes)\n\n";

