import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import {
    destroy,
    store,
    toggle,
} from '@/actions/App/Http/Controllers/Admin/PaymentMethodController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Method = {
    id: number;
    type: 'bank' | 'qris';
    name: string;
    account_number: string | null;
    account_holder: string | null;
    qris_url: string | null;
    is_active: boolean;
};

export default function PaymentMethodsIndex({
    methods,
}: {
    methods: Method[];
}) {
    const [type, setType] = useState<'bank' | 'qris'>('bank');

    return (
        <>
            <Head title="Metode pembayaran" />
            <div className="space-y-6 p-4">
                <h1 className="text-2xl font-semibold">Metode pembayaran</h1>

                <div className="space-y-3">
                    {methods.length === 0 && (
                        <p className="text-muted-foreground">
                            Belum ada metode pembayaran. Tambahkan minimal satu
                            agar pengunjung bisa memesan.
                        </p>
                    )}
                    {methods.map((method) => (
                        <div
                            key={method.id}
                            className="flex flex-wrap items-center gap-4 rounded-lg border p-4"
                        >
                            {method.qris_url && (
                                <img
                                    src={method.qris_url}
                                    alt="QRIS"
                                    className="size-20 rounded border object-contain"
                                />
                            )}
                            <div className="mr-auto text-sm">
                                <p className="font-medium">
                                    {method.name}{' '}
                                    <Badge variant="secondary">
                                        {method.type === 'qris'
                                            ? 'QRIS'
                                            : 'Rekening'}
                                    </Badge>
                                </p>
                                {method.type === 'bank' && (
                                    <p>
                                        {method.account_number} · a.n.{' '}
                                        {method.account_holder}
                                    </p>
                                )}
                            </div>
                            <Form {...toggle.form(method.id)}>
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        variant={
                                            method.is_active
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                        disabled={processing}
                                    >
                                        {method.is_active
                                            ? 'Aktif'
                                            : 'Nonaktif'}
                                    </Button>
                                )}
                            </Form>
                            <Form {...destroy.form(method.id)}>
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                    >
                                        Hapus
                                    </Button>
                                )}
                            </Form>
                        </div>
                    ))}
                </div>

                <h2 className="text-lg font-medium">Tambah metode</h2>
                <Form
                    {...store.form()}
                    resetOnSuccess
                    className="grid max-w-md gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="type">Jenis</Label>
                                <select
                                    id="type"
                                    name="type"
                                    value={type}
                                    onChange={(event) =>
                                        setType(
                                            event.target.value as
                                                | 'bank'
                                                | 'qris',
                                        )
                                    }
                                    className="h-9 rounded-md border bg-transparent px-3 text-sm"
                                >
                                    <option value="bank">Rekening bank</option>
                                    <option value="qris">QRIS</option>
                                </select>
                                <InputError message={errors.type} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    {type === 'bank' ? 'Nama bank' : 'Nama'}
                                </Label>
                                <Input
                                    id="name"
                                    name="name"
                                    placeholder={
                                        type === 'bank' ? 'BCA' : 'QRIS Desa'
                                    }
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            {type === 'bank' ? (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="account_number">
                                            Nomor rekening
                                        </Label>
                                        <Input
                                            id="account_number"
                                            name="account_number"
                                        />
                                        <InputError
                                            message={errors.account_number}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="account_holder">
                                            Atas nama
                                        </Label>
                                        <Input
                                            id="account_holder"
                                            name="account_holder"
                                        />
                                        <InputError
                                            message={errors.account_holder}
                                        />
                                    </div>
                                </>
                            ) : (
                                <div className="grid gap-2">
                                    <Label htmlFor="qris">
                                        Gambar QRIS (JPG, PNG, WEBP)
                                    </Label>
                                    <Input
                                        id="qris"
                                        name="qris"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp"
                                    />
                                    <InputError message={errors.qris} />
                                </div>
                            )}
                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                Tambah
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
