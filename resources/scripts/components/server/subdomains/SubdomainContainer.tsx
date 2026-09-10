import React, { useCallback, useEffect, useState } from 'react';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import Can from '@/components/elements/Can';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import MessageBox from '@/components/MessageBox';
import useFlash from '@/plugins/useFlash';
import { createSubdomain, deleteSubdomain, getSubdomains, SubdomainPayload } from '@/api/server/subdomains';

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addFlash, clearAndAddHttpError } = useFlash();
    const [data, setData] = useState<SubdomainPayload | null>(null);
    const [label, setLabel] = useState('');
    const [domainId, setDomainId] = useState('');
    const [allocationId, setAllocationId] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [deleting, setDeleting] = useState<number | null>(null);

    const load = useCallback(() => {
        setLoading(true);
        getSubdomains(uuid)
            .then(setData)
            .catch((error) => clearAndAddHttpError({ error, key: 'subdomains' }))
            .finally(() => setLoading(false));
    }, [uuid]);

    useEffect(load, [load]);

    const submit = async (event: React.FormEvent) => {
        event.preventDefault();
        if (!domainId || !allocationId) return;

        setSaving(true);
        clearFlashes('subdomains');
        try {
            await createSubdomain(uuid, label, Number(domainId), Number(allocationId));
            setLabel('');
            addFlash({
                key: 'subdomains',
                type: 'success',
                title: 'Success',
                message: 'Subdomain created successfully.',
            });
            load();
        } catch (error) {
            clearAndAddHttpError({ error, key: 'subdomains' });
        } finally {
            setSaving(false);
        }
    };

    const remove = async (id: number) => {
        if (!window.confirm('Delete this subdomain and its DNS records?')) return;
        setDeleting(id);
        try {
            await deleteSubdomain(uuid, id);
            addFlash({
                key: 'subdomains',
                type: 'success',
                title: 'Success',
                message: 'Subdomain deleted successfully.',
            });
            load();
        } catch (error) {
            clearAndAddHttpError({ error, key: 'subdomains' });
        } finally {
            setDeleting(null);
        }
    };

    return (
        <ServerContentBlock title={'Subdomain'}>
            {loading && !data ? (
                <Spinner size={'large'} centered />
            ) : (
                data && (
                    <>
                        <div css={tw`mb-6`}>
                            <h2 css={tw`text-xl text-neutral-100 mb-2`}>Subdomains</h2>
                            <p css={tw`text-sm text-neutral-300`}>
                                Create a Minecraft hostname linked to one of this server&apos;s allocations.
                            </p>
                        </div>
                        <Can action={'subdomain.manage'}>
                            <form onSubmit={submit} css={tw`bg-neutral-700 rounded p-4 mb-6`}>
                                <div css={tw`grid grid-cols-1 md:grid-cols-4 gap-4 items-end`}>
                                    <div>
                                        <Label>Subdomain</Label>
                                        <Input
                                            value={label}
                                            onChange={(event) => setLabel(event.target.value)}
                                            placeholder={'play'}
                                            required
                                        />
                                    </div>
                                    <div>
                                        <Label>Domain</Label>
                                        <Select
                                            value={domainId}
                                            onChange={(event) => setDomainId(event.target.value)}
                                            required
                                        >
                                            <option value=''>Select domain</option>
                                            {data.domains.map((domain) => (
                                                <option key={domain.id} value={domain.id}>
                                                    {domain.name}
                                                </option>
                                            ))}
                                        </Select>
                                    </div>
                                    <div>
                                        <Label>Allocation</Label>
                                        <Select
                                            value={allocationId}
                                            onChange={(event) => setAllocationId(event.target.value)}
                                            required
                                        >
                                            <option value=''>Select allocation</option>
                                            {data.allocations.map((allocation) => (
                                                <option key={allocation.id} value={allocation.id}>
                                                    {allocation.address}
                                                </option>
                                            ))}
                                        </Select>
                                    </div>
                                    <Button type={'submit'} disabled={saving} isLoading={saving}>
                                        Create subdomain
                                    </Button>
                                </div>
                            </form>
                        </Can>
                        {data.subdomains.length === 0 ? (
                            <MessageBox type={'info'}>No subdomains have been created for this server.</MessageBox>
                        ) : (
                            <div css={tw`space-y-3`}>
                                {data.subdomains.map((item) => (
                                    <div
                                        key={item.id}
                                        css={tw`bg-neutral-700 rounded p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4`}
                                    >
                                        <div>
                                            <div css={tw`text-xs uppercase text-neutral-400`}>Subdomain</div>
                                            <code css={tw`text-neutral-100`}>{item.fqdn}</code>
                                        </div>
                                        <div>
                                            <div css={tw`text-xs uppercase text-neutral-400`}>Allocation</div>
                                            <code css={tw`text-neutral-100`}>{item.allocation}</code>
                                        </div>
                                        <Can action={'subdomain.manage'}>
                                            <Button
                                                color={'red'}
                                                size={'small'}
                                                disabled={deleting !== null}
                                                isLoading={deleting === item.id}
                                                onClick={() => remove(item.id)}
                                            >
                                                Delete
                                            </Button>
                                        </Can>
                                    </div>
                                ))}
                            </div>
                        )}
                    </>
                )
            )}
        </ServerContentBlock>
    );
};
