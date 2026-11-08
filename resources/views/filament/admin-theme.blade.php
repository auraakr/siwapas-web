{{--
    Makes the admin panel layout follow the visitor dashboard (starter kit): a compact sidebar with small
    links and icons, the account menu at the bottom of the sidebar, and a quiet top bar. Colours come
    from the landing page palette.
--}}
<style>
    /* Page background: landing page cream (light mode only). */
    html:not(.dark) .fi-body {
        background-color: #faf6ec;
    }

    /* Sidebar: borderless, same background as the page, compact header. */
    .fi-sidebar-header {
        height: 3.5rem;
        padding-inline: 1rem;
        background-color: transparent;
        box-shadow: none;
    }

    html:not(.dark) .fi-sidebar-header {
        --tw-ring-shadow: 0 0 #0000;
    }

    .fi-sidebar-nav {
        padding: 0.5rem !important;
        row-gap: 1rem !important;
    }

    .fi-sidebar-nav-groups {
        margin-inline: 0 !important;
        row-gap: 1rem !important;
    }

    /* Navigation links: 2rem tall, 16px icons, 14px text. */
    .fi-sidebar-item-button {
        min-height: 2rem;
        gap: 0.5rem !important;
        padding: 0.375rem 0.5rem !important;
        border-radius: 0.375rem !important;
    }

    .fi-sidebar-item-icon {
        width: 1rem !important;
        height: 1rem !important;
    }

    .fi-sidebar-item-label {
        font-size: 0.875rem;
        line-height: 1.25rem;
        font-weight: 500;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
        background-color: rgb(var(--primary-100));
    }

    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
        background-color: rgb(255 255 255 / 0.08);
    }

    /* Group labels: small and quiet. */
    .fi-sidebar-group-button {
        padding: 0.25rem 0.5rem !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.75rem !important;
        line-height: 1rem !important;
        font-weight: 500 !important;
    }

    .fi-sidebar-group-icon {
        width: 1rem !important;
        height: 1rem !important;
    }

    /* Top bar: short, flat and without the user menu (it lives in the sidebar). */
    .fi-topbar > nav {
        height: 3.5rem;
        background-color: transparent;
        box-shadow: none;
        border-bottom: 1px solid rgb(var(--gray-200));
    }

    .dark .fi-topbar > nav {
        border-bottom-color: rgb(255 255 255 / 0.1);
    }

    .fi-topbar .fi-user-menu {
        display: none;
    }

    /*
     * Brand and account menu. The panel ships precompiled CSS, so these views use their own classes
     * instead of Tailwind utilities that may not exist in that stylesheet.
     */
    .sw-brand {
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .sw-brand-mark {
        display: flex;
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background-color: rgb(var(--primary-800));
        color: #fff;
    }

    .sw-brand-mark svg {
        width: 1rem;
        height: 1rem;
    }

    .sw-brand-text {
        display: grid;
        text-align: start;
        line-height: 1.15;
    }

    .sw-brand-name {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        color: rgb(var(--primary-900));
    }

    .sw-brand-sub {
        font-size: 0.625rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: rgb(var(--gray-500));
    }

    .dark .sw-brand-name {
        color: #fff;
    }

    .dark .sw-brand-sub {
        color: rgb(var(--gray-400));
    }

    .sw-account {
        border-top: 1px solid rgb(var(--gray-200));
        padding: 0.5rem;
    }

    .dark .sw-account {
        border-top-color: rgb(255 255 255 / 0.1);
    }

    .sw-account .fi-dropdown {
        display: block;
        width: 100%;
    }

    .sw-account-trigger {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: 0.375rem;
        text-align: start;
        transition: background-color 0.15s;
    }

    .sw-account-trigger:hover,
    .sw-account-trigger:focus-visible {
        background-color: rgb(var(--gray-100));
        outline: none;
    }

    .dark .sw-account-trigger:hover,
    .dark .sw-account-trigger:focus-visible {
        background-color: rgb(255 255 255 / 0.08);
    }

    .sw-avatar {
        display: flex;
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background-color: rgb(var(--primary-100));
        color: rgb(var(--primary-800));
        font-size: 0.75rem;
        font-weight: 600;
    }

    .dark .sw-avatar {
        background-color: rgb(var(--primary-800));
        color: #fff;
    }

    .sw-account-meta {
        display: grid;
        flex: 1;
        min-width: 0;
        line-height: 1.25;
    }

    .sw-account-name,
    .sw-account-email {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sw-account-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: rgb(var(--gray-950));
    }

    .sw-account-email {
        font-size: 0.75rem;
        color: rgb(var(--gray-500));
    }

    .dark .sw-account-name {
        color: #fff;
    }

    .dark .sw-account-email {
        color: rgb(var(--gray-400));
    }

    .sw-account-chevron {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        color: rgb(var(--gray-400));
    }

    .sw-menu-head {
        display: grid;
        padding: 0.5rem 0.75rem;
        line-height: 1.25;
    }
</style>
