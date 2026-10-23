/**
 * Fingerprint sign-in through the Android app's `window.WebToApk` bridge.
 * In a normal browser the bridge is missing and every call reports "unavailable".
 */

export type BiometricStatus = 'unavailable' | 'available' | 'enrolled';

export type BiometricResult = {
    action: 'enroll' | 'unlock';
    status: 'ok' | 'cancelled' | 'failed' | 'invalidated' | 'unavailable';
    token: string | null;
};

type WebToApkBridge = {
    biometricStatus(): BiometricStatus;
    biometricEnroll(token: string): void;
    biometricUnlock(): void;
    biometricClear(): void;
};

function bridge(): WebToApkBridge | null {
    const candidate = (window as { WebToApk?: Partial<WebToApkBridge> })
        .WebToApk;

    return typeof candidate?.biometricStatus === 'function'
        ? (candidate as WebToApkBridge)
        : null;
}

function waitForResult(
    action: BiometricResult['action'],
): Promise<BiometricResult> {
    return new Promise((resolve) => {
        const listener = (event: Event) => {
            const result = (event as CustomEvent<BiometricResult>).detail;

            if (result?.action !== action) {
                return;
            }

            window.removeEventListener('webtoapk:biometric', listener);
            resolve(result);
        };

        window.addEventListener('webtoapk:biometric', listener);
    });
}

export function biometricStatus(): BiometricStatus {
    try {
        return bridge()?.biometricStatus() ?? 'unavailable';
    } catch {
        return 'unavailable';
    }
}

export function enrollBiometric(token: string): Promise<BiometricResult> {
    const app = bridge();

    if (!app) {
        return Promise.resolve({
            action: 'enroll',
            status: 'unavailable',
            token: null,
        });
    }

    const result = waitForResult('enroll');
    app.biometricEnroll(token);

    return result;
}

export function unlockBiometric(): Promise<BiometricResult> {
    const app = bridge();

    if (!app) {
        return Promise.resolve({
            action: 'unlock',
            status: 'unavailable',
            token: null,
        });
    }

    const result = waitForResult('unlock');
    app.biometricUnlock();

    return result;
}

export function clearBiometric(): void {
    bridge()?.biometricClear();
}
