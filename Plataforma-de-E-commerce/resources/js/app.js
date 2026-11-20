

import Alpine from 'alpinejs';
import customerNotifications from './notifications';

window.Alpine = Alpine;

Alpine.data('customerNotifications', customerNotifications);

Alpine.start();
