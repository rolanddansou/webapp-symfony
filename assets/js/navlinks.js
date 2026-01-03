import {usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import {
    goToBecomeMerchant, goToContact, goToFaq,
    goToHomePage
} from "@/router";

const page = usePage()

const menuLinks= ref([
    {
        name: 'Accueil',
        route: ['home'],
        link: goToHomePage()
    },
    {
        name: 'Devenir commerçant',
        route: ['app_become_merchant', 'app_become_merchant_submit'],
        link: goToBecomeMerchant()
    },
    {
        name: 'FAQ',
        route: ['app_faq'],
        link: goToFaq()
    },
    {
        name: 'Contact',
        route: ['app_contact'],
        link: goToContact()
    },
]);

export default menuLinks;
