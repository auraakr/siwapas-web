export type Contact = {
    phone?: string;
    whatsapp?: string;
    whatsapp_url?: string;
    email?: string;
    address?: string;
    hours?: string;
    maps_url?: string;
    instagram?: string;
    facebook?: string;
    youtube?: string;
};

export type Destination = {
    id: number;
    name: string;
    summary: string | null;
    address: string | null;
    image: string | null;
    is_featured: boolean;
    requires_reservation: boolean;
};

export type FacilitySummary = {
    slug: string;
    name: string;
    capacity: number | null;
    base_price: number;
};

export type Product = {
    id: number;
    name: string;
    maker: string | null;
    description: string | null;
    image: string | null;
    images: string[];
    whatsapp_url: string | null;
};

export type PosterItem = {
    id: number;
    title: string;
    alt: string;
    image: string;
};

export type EventSummary = {
    slug: string;
    title: string;
    starts_at: string;
    location_name: string | null;
    fee: number;
};

export type ArticleSummary = {
    id: number;
    title: string;
    excerpt: string | null;
    category: string;
    author: string | null;
    published_at: string | null;
    image: string | null;
};

export type Faq = {
    question: string;
    answer: string;
};

/** Opening hours and admission text from Pengaturan Situs. */
export type VisitSummary = {
    hours: string | null;
    admission: string;
};

/** A facility the managers suggest for visitors who want to hold their own event. */
export type EventVenue = {
    slug: string;
    name: string;
};

export type LandingProps = {
    images: { hero: string | null; about: string | null };
    video_url: string | null;
    visit: VisitSummary;
    destinations: Destination[];
    facilities: FacilitySummary[];
    event_venues: EventVenue[];
    products: Product[];
    posters: PosterItem[];
    events: EventSummary[];
    articles: ArticleSummary[];
    faqs: Faq[];
};
