<?php

namespace App\Support;

/**
 * Centralizes all UI and email strings of the newsletter feature.
 *
 * Follows the same per-language array pattern used by the docs controllers
 * (ui_strings), so the newsletter screens and mailables share one source.
 */
final class NewsletterTranslations
{
    public const LANGS = ['en', 'pt_br', 'pt_pt', 'fr', 'es'];

    public static function all(): array
    {
        return [
            'en' => [
                // Signup box
                'newsletter_box_title' => 'Get news about new versions',
                'newsletter_box_desc' => 'Sign up to receive an email whenever a new version of GitPR is released.',
                'newsletter_email_placeholder' => 'your@email.com',
                'newsletter_subscribe_btn' => 'Subscribe',
                'newsletter_sent' => 'Confirmation email sent! Check your inbox to complete the subscription.',
                'newsletter_already_confirmed' => 'This email is already subscribed.',
                'newsletter_send_cancel_link_btn' => 'Send cancellation link',
                'newsletter_cancel_link_sent' => 'If the email is registered, a cancellation link has been sent to it.',
                'newsletter_error' => 'Something went wrong. Please try again.',
                // Confirmation screen
                'confirm_title' => 'Confirm your subscription',
                'confirm_intro' => 'Fill in your details to complete the subscription.',
                'name_label' => 'Name',
                'email_label' => 'Email',
                'github_label' => 'GitHub username (optional)',
                'phone_label' => 'Phone (optional)',
                'lang_label' => 'Language',
                'confirm_submit_btn' => 'Confirm subscription',
                'confirm_cancel_note' => 'You can cancel your subscription at any time.',
                'confirm_expired_title' => 'Link expired',
                'confirm_expired_message' => 'This confirmation link has expired. Subscribe again to receive a new one.',
                'confirm_not_found_title' => 'Link not found',
                'confirm_not_found_message' => 'This confirmation link is invalid.',
                'confirm_already_title' => 'Subscription confirmed',
                'confirm_already_message' => 'This email has already been confirmed. You will receive the next newsletter in your inbox.',
                // Cancel screen
                'cancel_title' => 'Cancel subscription',
                'cancel_intro' => 'Do you want to stop receiving GitPR newsletters?',
                'cancel_btn' => 'Cancel subscription',
                'cancel_done_title' => 'Subscription canceled',
                'cancel_done_message' => 'You will no longer receive GitPR newsletters. You can subscribe again at any time.',
                'cancel_already_title' => 'Already canceled',
                'cancel_not_found_title' => 'Subscription not found',
                // Emails
                'mail_confirm_subject' => 'Confirm your GitPR newsletter subscription',
                'mail_confirm_intro' => 'Click the button below to confirm your subscription to the GitPR newsletter.',
                'mail_confirm_button' => 'Confirm subscription',
                'mail_confirm_note_24h' => 'This link expires in 24 hours.',
                'mail_cancel_subject' => 'Cancellation link for the GitPR newsletter',
                'mail_cancel_intro' => 'Click the button below to cancel your subscription to the GitPR newsletter.',
                'mail_cancel_button' => 'Cancel subscription',
                'mail_newsletter_subject' => '{version} — GitPR Newsletter',
                'mail_newsletter_unsubscribe' => 'Unsubscribe',
            ],
            'pt_br' => [
                // Box de cadastro
                'newsletter_box_title' => 'Receba novidades das novas versões',
                'newsletter_box_desc' => 'Cadastre-se para receber um e-mail sempre que uma nova versão do GitPR for lançada.',
                'newsletter_email_placeholder' => 'seu@email.com',
                'newsletter_subscribe_btn' => 'Assinar',
                'newsletter_sent' => 'E-mail de confirmação enviado! Verifique sua caixa de entrada para concluir a inscrição.',
                'newsletter_already_confirmed' => 'Este e-mail já está inscrito.',
                'newsletter_send_cancel_link_btn' => 'Enviar link de cancelamento',
                'newsletter_cancel_link_sent' => 'Se o e-mail estiver cadastrado, um link de cancelamento foi enviado para ele.',
                'newsletter_error' => 'Algo deu errado. Tente novamente.',
                // Tela de confirmação
                'confirm_title' => 'Confirme sua inscrição',
                'confirm_intro' => 'Preencha seus dados para concluir a inscrição.',
                'name_label' => 'Nome',
                'email_label' => 'E-mail',
                'github_label' => 'Usuário do GitHub (opcional)',
                'phone_label' => 'Telefone (opcional)',
                'lang_label' => 'Idioma',
                'confirm_submit_btn' => 'Confirmar inscrição',
                'confirm_cancel_note' => 'Você pode cancelar sua inscrição a qualquer momento.',
                'confirm_expired_title' => 'Link expirado',
                'confirm_expired_message' => 'Este link de confirmação expirou. Inscreva-se novamente para receber um novo.',
                'confirm_not_found_title' => 'Link não encontrado',
                'confirm_not_found_message' => 'Este link de confirmação é inválido.',
                'confirm_already_title' => 'Inscrição confirmada',
                'confirm_already_message' => 'Este e-mail já foi confirmado. Você receberá a próxima newsletter na sua caixa de entrada.',
                // Tela de cancelamento
                'cancel_title' => 'Cancelar inscrição',
                'cancel_intro' => 'Deseja parar de receber as newsletters do GitPR?',
                'cancel_btn' => 'Cancelar inscrição',
                'cancel_done_title' => 'Inscrição cancelada',
                'cancel_done_message' => 'Você não receberá mais as newsletters do GitPR. Pode se inscrever novamente a qualquer momento.',
                'cancel_already_title' => 'Já cancelada',
                'cancel_not_found_title' => 'Inscrição não encontrada',
                // E-mails
                'mail_confirm_subject' => 'Confirme sua inscrição na newsletter do GitPR',
                'mail_confirm_intro' => 'Clique no botão abaixo para confirmar sua inscrição na newsletter do GitPR.',
                'mail_confirm_button' => 'Confirmar inscrição',
                'mail_confirm_note_24h' => 'Este link expira em 24 horas.',
                'mail_cancel_subject' => 'Link de cancelamento da newsletter do GitPR',
                'mail_cancel_intro' => 'Clique no botão abaixo para cancelar sua inscrição na newsletter do GitPR.',
                'mail_cancel_button' => 'Cancelar inscrição',
                'mail_newsletter_subject' => '{version} — Newsletter do GitPR',
                'mail_newsletter_unsubscribe' => 'Cancelar inscrição',
            ],
            'pt_pt' => [
                // Caixa de subscrição
                'newsletter_box_title' => 'Receba novidades das novas versões',
                'newsletter_box_desc' => 'Subscreva para receber um e-mail sempre que uma nova versão do GitPR for lançada.',
                'newsletter_email_placeholder' => 'seu@email.com',
                'newsletter_subscribe_btn' => 'Subscrever',
                'newsletter_sent' => 'E-mail de confirmação enviado! Verifique a sua caixa de entrada para concluir a subscrição.',
                'newsletter_already_confirmed' => 'Este e-mail já está subscrito.',
                'newsletter_send_cancel_link_btn' => 'Enviar link de cancelamento',
                'newsletter_cancel_link_sent' => 'Se o e-mail estiver registado, foi enviado um link de cancelamento para o mesmo.',
                'newsletter_error' => 'Algo correu mal. Tente novamente.',
                // Tela de confirmação
                'confirm_title' => 'Confirme a sua subscrição',
                'confirm_intro' => 'Preencha os seus dados para concluir a subscrição.',
                'name_label' => 'Nome',
                'email_label' => 'E-mail',
                'github_label' => 'Utilizador do GitHub (opcional)',
                'phone_label' => 'Telefone (opcional)',
                'lang_label' => 'Idioma',
                'confirm_submit_btn' => 'Confirmar subscrição',
                'confirm_cancel_note' => 'Pode cancelar a sua subscrição a qualquer momento.',
                'confirm_expired_title' => 'Link expirado',
                'confirm_expired_message' => 'Este link de confirmação expirou. Subscreva novamente para receber um novo.',
                'confirm_not_found_title' => 'Link não encontrado',
                'confirm_not_found_message' => 'Este link de confirmação é inválido.',
                'confirm_already_title' => 'Subscrição confirmada',
                'confirm_already_message' => 'Este e-mail já foi confirmado. Receberá a próxima newsletter na sua caixa de entrada.',
                // Tela de cancelamento
                'cancel_title' => 'Cancelar subscrição',
                'cancel_intro' => 'Deseja deixar de receber as newsletters do GitPR?',
                'cancel_btn' => 'Cancelar subscrição',
                'cancel_done_title' => 'Subscrição cancelada',
                'cancel_done_message' => 'Não receberá mais as newsletters do GitPR. Pode subscrever novamente a qualquer momento.',
                'cancel_already_title' => 'Já cancelada',
                'cancel_not_found_title' => 'Subscrição não encontrada',
                // E-mails
                'mail_confirm_subject' => 'Confirme a sua subscrição na newsletter do GitPR',
                'mail_confirm_intro' => 'Clique no botão abaixo para confirmar a sua subscrição na newsletter do GitPR.',
                'mail_confirm_button' => 'Confirmar subscrição',
                'mail_confirm_note_24h' => 'Este link expira em 24 horas.',
                'mail_cancel_subject' => 'Link de cancelamento da newsletter do GitPR',
                'mail_cancel_intro' => 'Clique no botão abaixo para cancelar a sua subscrição na newsletter do GitPR.',
                'mail_cancel_button' => 'Cancelar subscrição',
                'mail_newsletter_subject' => '{version} — Newsletter do GitPR',
                'mail_newsletter_unsubscribe' => 'Cancelar subscrição',
            ],
            'fr' => [
                // Encart d'inscription
                'newsletter_box_title' => 'Recevez les nouveautés des versions',
                'newsletter_box_desc' => 'Inscrivez-vous pour recevoir un e-mail à chaque nouvelle version de GitPR.',
                'newsletter_email_placeholder' => 'votre@email.com',
                'newsletter_subscribe_btn' => "S'abonner",
                'newsletter_sent' => "E-mail de confirmation envoyé ! Vérifiez votre boîte de réception pour terminer l'inscription.",
                'newsletter_already_confirmed' => 'Cet e-mail est déjà inscrit.',
                'newsletter_send_cancel_link_btn' => 'Envoyer le lien de désinscription',
                'newsletter_cancel_link_sent' => "Si l'e-mail est enregistré, un lien de désinscription lui a été envoyé.",
                'newsletter_error' => "Une erreur s'est produite. Veuillez réessayer.",
                // Écran de confirmation
                'confirm_title' => 'Confirmez votre inscription',
                'confirm_intro' => "Remplissez vos informations pour terminer l'inscription.",
                'name_label' => 'Nom',
                'email_label' => 'E-mail',
                'github_label' => 'Identifiant GitHub (facultatif)',
                'phone_label' => 'Téléphone (facultatif)',
                'lang_label' => 'Langue',
                'confirm_submit_btn' => "Confirmer l'inscription",
                'confirm_cancel_note' => 'Vous pouvez annuler votre inscription à tout moment.',
                'confirm_expired_title' => 'Lien expiré',
                'confirm_expired_message' => 'Ce lien de confirmation a expiré. Inscrivez-vous à nouveau pour en recevoir un nouveau.',
                'confirm_not_found_title' => 'Lien introuvable',
                'confirm_not_found_message' => 'Ce lien de confirmation est invalide.',
                'confirm_already_title' => 'Inscription confirmée',
                'confirm_already_message' => 'Cet e-mail a déjà été confirmé. Vous recevrez la prochaine newsletter dans votre boîte de réception.',
                // Écran d'annulation
                'cancel_title' => "Annuler l'inscription",
                'cancel_intro' => 'Voulez-vous arrêter de recevoir les newsletters GitPR ?',
                'cancel_btn' => "Annuler l'inscription",
                'cancel_done_title' => 'Inscription annulée',
                'cancel_done_message' => 'Vous ne recevrez plus les newsletters GitPR. Vous pouvez vous réinscrire à tout moment.',
                'cancel_already_title' => 'Déjà annulée',
                'cancel_not_found_title' => 'Inscription introuvable',
                // E-mails
                'mail_confirm_subject' => 'Confirmez votre inscription à la newsletter GitPR',
                'mail_confirm_intro' => 'Cliquez sur le bouton ci-dessous pour confirmer votre inscription à la newsletter GitPR.',
                'mail_confirm_button' => "Confirmer l'inscription",
                'mail_confirm_note_24h' => 'Ce lien expire dans 24 heures.',
                'mail_cancel_subject' => 'Lien de désinscription de la newsletter GitPR',
                'mail_cancel_intro' => 'Cliquez sur le bouton ci-dessous pour annuler votre inscription à la newsletter GitPR.',
                'mail_cancel_button' => "Annuler l'inscription",
                'mail_newsletter_subject' => '{version} — Newsletter GitPR',
                'mail_newsletter_unsubscribe' => 'Se désinscrire',
            ],
            'es' => [
                // Caja de suscripción
                'newsletter_box_title' => 'Recibe novedades de las nuevas versiones',
                'newsletter_box_desc' => 'Suscríbete para recibir un correo cada vez que se publique una nueva versión de GitPR.',
                'newsletter_email_placeholder' => 'tu@email.com',
                'newsletter_subscribe_btn' => 'Suscribirse',
                'newsletter_sent' => '¡Correo de confirmación enviado! Revisa tu bandeja de entrada para completar la suscripción.',
                'newsletter_already_confirmed' => 'Este correo ya está suscrito.',
                'newsletter_send_cancel_link_btn' => 'Enviar enlace de cancelación',
                'newsletter_cancel_link_sent' => 'Si el correo está registrado, se ha enviado un enlace de cancelación.',
                'newsletter_error' => 'Algo salió mal. Inténtalo de nuevo.',
                // Pantalla de confirmación
                'confirm_title' => 'Confirma tu suscripción',
                'confirm_intro' => 'Completa tus datos para terminar la suscripción.',
                'name_label' => 'Nombre',
                'email_label' => 'Correo electrónico',
                'github_label' => 'Usuario de GitHub (opcional)',
                'phone_label' => 'Teléfono (opcional)',
                'lang_label' => 'Idioma',
                'confirm_submit_btn' => 'Confirmar suscripción',
                'confirm_cancel_note' => 'Puedes cancelar tu suscripción en cualquier momento.',
                'confirm_expired_title' => 'Enlace caducado',
                'confirm_expired_message' => 'Este enlace de confirmación ha caducado. Suscríbete de nuevo para recibir otro.',
                'confirm_not_found_title' => 'Enlace no encontrado',
                'confirm_not_found_message' => 'Este enlace de confirmación no es válido.',
                'confirm_already_title' => 'Suscripción confirmada',
                'confirm_already_message' => 'Este correo ya ha sido confirmado. Recibirás la próxima newsletter en tu bandeja de entrada.',
                // Pantalla de cancelación
                'cancel_title' => 'Cancelar suscripción',
                'cancel_intro' => '¿Quieres dejar de recibir las newsletters de GitPR?',
                'cancel_btn' => 'Cancelar suscripción',
                'cancel_done_title' => 'Suscripción cancelada',
                'cancel_done_message' => 'Ya no recibirás las newsletters de GitPR. Puedes volver a suscribirte en cualquier momento.',
                'cancel_already_title' => 'Ya cancelada',
                'cancel_not_found_title' => 'Suscripción no encontrada',
                // E-mails
                'mail_confirm_subject' => 'Confirma tu suscripción a la newsletter de GitPR',
                'mail_confirm_intro' => 'Haz clic en el botón de abajo para confirmar tu suscripción a la newsletter de GitPR.',
                'mail_confirm_button' => 'Confirmar suscripción',
                'mail_confirm_note_24h' => 'Este enlace caduca en 24 horas.',
                'mail_cancel_subject' => 'Enlace de cancelación de la newsletter de GitPR',
                'mail_cancel_intro' => 'Haz clic en el botón de abajo para cancelar tu suscripción a la newsletter de GitPR.',
                'mail_cancel_button' => 'Cancelar suscripción',
                'mail_newsletter_subject' => '{version} — Newsletter de GitPR',
                'mail_newsletter_unsubscribe' => 'Cancelar suscripción',
            ],
        ];
    }

    public static function for(string $lang): array
    {
        $all = self::all();

        return $all[$lang] ?? $all['en'];
    }

    public static function get(string $lang, string $key, array $replace = []): string
    {
        $strings = self::for($lang);
        $text = $strings[$key] ?? self::for('en')[$key] ?? $key;

        return empty($replace) ? $text : strtr($text, $replace);
    }
}
