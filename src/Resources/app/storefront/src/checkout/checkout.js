import Plugin from 'src/plugin-system/plugin.class'
import { getCsrfToken, createHiddenField } from '../util.js'

export default class EasyCreditRatenkaufCheckout extends Plugin {
    init() {
        document.querySelector('easycredit-checkout')?.addEventListener('submit', async (e) => {
            var form = document.getElementById('changePaymentForm')

            let token = await getCsrfToken()
            if (token) {
              form.append(createHiddenField('_csrf_token', token))
            }

            form.append(createHiddenField('easycredit[submit]','1'))
            form.append(createHiddenField('easycredit[number-of-installments]', e.detail.numberOfInstallments))
            form.submit()

            return false
        })
    }
}
