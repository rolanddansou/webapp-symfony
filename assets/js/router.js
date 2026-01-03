import Routing from 'fos-router';
import routes from './routes.json'

Routing.setRoutingData(routes);

export default Routing;

export function goToHomePage() {
    return Routing.generate('home');
}

export function goToTerms() {
    return Routing.generate('app_static_content', { type: 'cgu' });
}

export function goToPrivacy() {
    return Routing.generate('app_static_content', { type: 'privacy' });
}

export function goToMentions() {
    return Routing.generate('app_static_content', { type: 'mentions' });
}

export function goToContact() {
    return Routing.generate('app_contact');
}

export function goToBecomeMerchant() {
    return Routing.generate('app_become_merchant');
}

export function goToBecomeMerchantSubmit() {
    return Routing.generate('app_become_merchant_submit');
}

export function goToFaq() {
    return Routing.generate('app_faq');
}
