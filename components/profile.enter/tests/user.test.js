import { Selector, ClientFunction } from 'testcafe';
import * as utils from './utils.js';

fixture `Пользователь`
    .page(utils.url);


let randomUserEmail = 'test' + ~~(Math.random()*100000) + '@mail.com'
let password = 'some stupid password' + ~~(Math.random()*100000)
let newPassword = 'another password' + ~~(Math.random()*100000)

const redirectedToPersonal = async (t) => await t.expect(utils.getPageUrl()).contains('/personal/', { timeout: 10000 })

const logout = async (t) => await t.navigateTo(utils.url + '/?logout=yes')

let loginIconSelector = 'a[data-popup="#login"]'

const testLogin = ({testName, login, password}) => {
    test(
        'Редирект в лк после авторизации ' + testName,
        async t => {
            await logout(t)

            await t
                .click(loginIconSelector)
                // .takeScreenshot()
                .typeText('form[name="authorize"] input[name="login"]', login)
                .typeText('form[name="authorize"] input[name="password"]', password)
                // .takeScreenshot()
                .click('form[name="authorize"] button[type="submit"]')

            await redirectedToPersonal(t)
        }
    )
}

test(
    'Редирект в лк после регистрации',
    async t => {
        await t
            .click(loginIconSelector)
            .click('form[name="authorize"] a[data-popup="#registration"]')
            // .takeScreenshot()
            .typeText('form[name="register"] input[name="name"]', 'ТестИмя')
            // .typeText('form[name="register"] input[name="REGISTER[LAST_NAME]"]', 'ТестФамилия')
            .typeText('form[name="register"] input[name="email"]', randomUserEmail)

            .typeText('form[name="register"] input[name="password"]', password)
            .typeText('form[name="register"] input[name="confirm_password"]', password)
            // .takeScreenshot()
            .click('form[name="register"] button[type="submit"]')

        await redirectedToPersonal(t)
    }
)

testLogin({
    testName: '(после регистрации)',
    login: randomUserEmail,
    password
})

test(
    'Редирект в лк после смены пароля',
    async t => {
        await logout(t)
        await t
            .click(loginIconSelector)
            .click('form[name="authorize"] a[data-popup="#forgot-pass"]')
            // .takeScreenshot()
            .typeText('form[name="restore"] input[name=email]', randomUserEmail)
            // .takeScreenshot()
            .click('form[name="restore"] button[type="submit"]')

        // открываем почту
        await utils.mailhog(t)
        await t
            .wait(1000)
            .expect(
                Selector('.messages > .msglist-message:nth-child(1) span.subject')
                .textContent
            ).contains('Запрос на смену пароля', { timeout: 10000 })
            // .takeScreenshot()
            .click('.messages > .msglist-message:nth-child(1)')
            // .takeScreenshot()
            .switchToIframe('#preview-html')

        // Достаем оттуда ссылку
        let params = await (ClientFunction(() => document.body.innerText
            .split('\n').find(l => l.includes('change_password=yes'))
            .split(' ').find(l => l.includes('change_password=yes'))
            .split('?')[1]))()

        // выходим из iframe и возвращаемся на сайт
        await t
            .switchToMainWindow()
            .navigateTo(utils.url + '?' + params)

        // придумываем новый пароль

        // вводим его
        await t
            // .takeScreenshot()
            .typeText('form[name="changePassword"] input[name="password"]', newPassword)
            .typeText('form[name="changePassword"] input[name="confirmPassword"]', newPassword)
            // .takeScreenshot()
            .click('form[name="changePassword"] button[type="submit"]')

        // ожидаем редиректа
        await redirectedToPersonal(t)
    }
)

testLogin({
    testName: '(после смены пароля)',
    login: randomUserEmail,
    password: newPassword
})
