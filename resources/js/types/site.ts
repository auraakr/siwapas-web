export type DestinationCard = {
    id: number;
    slug: string;
    name: string;
    summary: string | null;
    address: string | null;
    image: string | null;
    is_featured: boolean;
    requires_reservation: boolean;
};

export type DestinationDetail = {
    slug: string;
    type: 'destination' | 'activity';
    name: string;
    summary: string | null;
    description: string | null;
    address: string | null;
    maps_url: string | null;
    video_url: string | null;
    requires_reservation: boolean;
    images: string[];
    opening_hours: { day: string; hours: string }[];
    facilities: string[];
    rules: string[];
};

export type GalleryEntry = {
    id: number;
    title: string | null;
    category: string | null;
    image: string;
    youtube_id: string | null;
};

export type TeamMember = {
    id: number;
    name: string;
    role: string;
    bio: string | null;
    image: string | null;
};

export type PageContent = {
    title: string;
    body: string | null;
    seo_title: string | null;
    seo_description: string | null;
};
