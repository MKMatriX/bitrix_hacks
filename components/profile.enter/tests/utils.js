import { Selector, ClientFunction } from 'testcafe';

export const getPageUrl = ClientFunction(() => window.location.href.toString());

export const url = 'http://site.local'
export const mailhogPort = 8025

export const mailhogNewWindow = async (t) => {
	return await t.openWindow(url + ":" + mailhogPort)
}

export const mailhog = async (t) => {
	return await t.navigateTo(url + ":" + mailhogPort)
}