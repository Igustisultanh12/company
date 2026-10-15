import counter from './counter';
import magnetic from './magnetic';
import reveal from './reveal';
import ripple from './ripple';
import tilt from './tilt';

export default {
    install(app) {
        app.directive('reveal', reveal);
        app.directive('magnetic', magnetic);
        app.directive('tilt', tilt);
        app.directive('counter', counter);
        app.directive('ripple', ripple);
    },
};
