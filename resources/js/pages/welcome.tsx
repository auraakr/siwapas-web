import { Head, usePage } from '@inertiajs/react';
import About from '@/components/landing/about';
import Destinations from '@/components/landing/destinations';
import Experience from '@/components/landing/experience';
import Events from '@/components/landing/events';
import FacilitiesBanner from '@/components/landing/facilities-banner';
import FinalCta from '@/components/landing/final-cta';
import Hero from '@/components/landing/hero';
import Partners from '@/components/landing/partners';
import Posters from '@/components/landing/posters';
import Products from '@/components/landing/products';
import Stories from '@/components/landing/stories';
import VisitInfo from '@/components/landing/visit-info';
import VisitStrip from '@/components/landing/visit-strip';
import HostEvent from '@/components/site/host-event';
import { useTranslation } from '@/lib/i18n';
import type { LandingProps } from '@/types/landing';

export default function Welcome({
    images,
    video_url,
    visit,
    destinations,
    facilities,
    event_venues,
    products,
    posters,
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
                <meta name="twitter:card" content="summary_large_image" />
                {images.hero && (
                    <meta property="og:image" content={images.hero} />
                )}
                <script type="application/ld+json">
                    {JSON.stringify(structuredData)}
                </script>
            </Head>

            <Hero image={images.hero} videoUrl={video_url} />
            <VisitStrip visit={visit} />
            <About image={images.about} address={contact.address} />
            <Partners />
            <Destinations destinations={destinations} />
            <Experience />
            <FacilitiesBanner items={facilities} />
            <Products products={products} />
            <Posters items={posters} />
            <Events items={events} />
            <HostEvent venues={event_venues} />
            <Stories articles={articles} />
            {/* Testimonials stay hidden until there are real visitor reviews. */}
            <VisitInfo visit={visit} contact={contact} faqs={faqs} />
            <FinalCta whatsappUrl={contact.whatsapp_url} />
        </>
    );
}
