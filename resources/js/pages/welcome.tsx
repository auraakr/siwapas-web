import { Head, usePage } from '@inertiajs/react';
import Activities from '@/components/landing/activities';
import About from '@/components/landing/about';
import Destinations from '@/components/landing/destinations';
import Events from '@/components/landing/events';
import FacilitiesBanner from '@/components/landing/facilities-banner';
import FinalCta from '@/components/landing/final-cta';
import Hero from '@/components/landing/hero';
import Products from '@/components/landing/products';
import Stories from '@/components/landing/stories';
import VisitInfo from '@/components/landing/visit-info';
import { useTranslation } from '@/lib/i18n';
import type { LandingProps } from '@/types/landing';

export default function Welcome({
    images,
    video_url,
    destinations,
    activities,
    facilities,
    products,
    events,
    articles,
    faqs,
}: LandingProps) {
    const { site } = usePage().props;
    const { t, locale } = useTranslation();
    const contact = site.contact;
    const description = t('meta.description');

    const structuredData = {
        '@context': 'https://schema.org',
        '@type': 'TouristDestination',
        name: 'Desa Wisata Pasirnanjung Geulis',
        description,
        inLanguage: locale,
        ...(images.hero && { image: images.hero }),
        ...(contact.address && { address: contact.address }),
        ...(contact.phone && { telephone: contact.phone }),
    };

    return (
        <>
            <Head title="Desa Wisata Pasirnanjung Geulis">
                <meta name="description" content={description} />
                <meta property="og:type" content="website" />
                <meta
                    property="og:title"
                    content="Desa Wisata Pasirnanjung Geulis"
                />
                <meta property="og:description" content={description} />
                <meta
                    property="og:locale"
                    content={locale === 'en' ? 'en_US' : 'id_ID'}
                />
                {images.hero && (
                    <meta property="og:image" content={images.hero} />
                )}
                <script type="application/ld+json">
                    {JSON.stringify(structuredData)}
                </script>
            </Head>

            <Hero image={images.hero} videoUrl={video_url} />
            <About image={images.about} address={contact.address} />
            <Destinations destinations={destinations} />
            <Activities
                activities={activities}
                whatsappUrl={contact.whatsapp_url}
            />
            <FacilitiesBanner items={facilities} />
            <Products products={products} />
            <Events items={events} />
            <Stories articles={articles} />
            <VisitInfo contact={contact} faqs={faqs} />
            <FinalCta whatsappUrl={contact.whatsapp_url} />
        </>
    );
}
