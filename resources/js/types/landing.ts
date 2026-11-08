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
    whatsapp_url: string | null;
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

export type LandingProps = {
    images: { hero: string | null; about: string | null };
    video_url: string | null;
    destinations: Destination[];
    activities: Destination[];
    facilities: FacilitySummary[];
    products: Product[];
    events: EventSummary[];
    articles: ArticleSummary[];
    faqs: Faq[];
};
