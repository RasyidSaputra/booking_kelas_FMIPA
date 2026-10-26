

import Alpine from 'alpinejs';
import adminMaster from './admin-master';

window.Alpine = Alpine;
Alpine.data('adminMaster', adminMaster);

Alpine.start();
