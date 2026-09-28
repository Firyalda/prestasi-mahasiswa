<?php
/**
 * Flash message helper functions
 * Provides session-based flash messaging for SweetAlert2
 */

function set_flash_message($type, $title, $text = '', $timer = null) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'title' => $title,
        'text' => $text,
        'timer' => $timer
    ];
}

function get_flash_message() {
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $message;
}

function render_flash_script() {
    $message = get_flash_message();

    if (!$message) {
        return;
    }

    $type = $message['type'] ?? 'info';
    $title = addslashes($message['title']);
    $text = addslashes($message['text'] ?? '');
    $timer = $message['timer'] ?? 'null';

    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const config = {
                icon: '<?php echo $type; ?>',
                title: '<?php echo $title; ?>',
                confirmButtonColor: '<?php echo $type === 'success' ? '#2c7da0' : ($type === 'error' ? '#dc2626' : '#3b82f6'); ?>',
                confirmButtonText: 'OK'
            };

            <?php if ($text): ?>
            config.text = '<?php echo $text; ?>';
            <?php endif; ?>

            <?php if ($timer !== 'null'): ?>
            config.timer = <?php echo $timer; ?>;
            config.timerProgressBar = true;
            <?php endif; ?>

            Swal.fire(config);
        });
    </script>
    <?php
}
?>
