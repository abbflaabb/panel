import React, { FormEvent, useState } from 'react';
import axios from 'axios';
import tw from 'twin.macro';
import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';
import useFlash from '@/plugins/useFlash';
import Can from '@/components/elements/Can';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerContext } from '@/state/server';
import pullFile from '@/api/server/files/pullFile';

interface PluginResult {
    id: string;
    name: string;
    description: string;
    author?: string;
    version?: string;
    downloads?: number;
    url: string;
    fileName: string;
    source: 'Modrinth' | 'Spigot';
}

type Provider = 'modrinth' | 'spigot' | 'builtbybit';

const api = axios.create({
    timeout: 15000,
    headers: { Accept: 'application/json', 'User-Agent': 'pterodactyl-panel-plugin-installer' },
});

const safeFileName = (name: string): string => {
    const fileName = name.split('/').pop() || 'plugin.jar';
    const cleaned = fileName.replace(/[^a-zA-Z0-9._-]/g, '-');
    return cleaned.toLowerCase().endsWith('.jar') ? cleaned : `${cleaned}.jar`;
};

const searchModrinth = async (query: string): Promise<PluginResult[]> => {
    const facets = JSON.stringify([['project_type:plugin']]);
    const { data } = await api.get('https://api.modrinth.com/v2/search', {
        params: { query, facets, limit: 20 },
    });

    const projects = await Promise.all(
        (data.hits || []).slice(0, 12).map(async (project: any) => {
            try {
                const versions = await api.get(`https://api.modrinth.com/v2/project/${project.slug}/version`, {
                    params: { loaders: 'paper,spigot,bukkit', featured: true, include_changelog: false },
                });
                const version = versions.data?.[0];
                const file = version?.files?.find((item: any) => item.primary) || version?.files?.[0];
                if (!file?.url || !file.filename.toLowerCase().endsWith('.jar')) return null;

                return {
                    id: project.project_id,
                    name: project.title,
                    description: project.description,
                    author: project.author,
                    version: version.version_number,
                    downloads: project.downloads,
                    url: file.url,
                    fileName: safeFileName(file.filename),
                    source: 'Modrinth' as const,
                };
            } catch {
                return null;
            }
        })
    );

    return projects.filter(Boolean) as PluginResult[];
};

const searchSpigot = async (query: string): Promise<PluginResult[]> => {
    const { data } = await api.get(`https://api.spiget.org/v2/search/resources/${encodeURIComponent(query)}`, {
        params: { size: 20, sort: '-downloads' },
    });

    return (data || []).slice(0, 20).map((resource: any) => ({
        id: String(resource.id),
        name: resource.name,
        description: resource.tag || 'Spigot resource',
        author: resource.author?.name,
        version: resource.version?.string,
        downloads: resource.downloads,
        url: `https://api.spiget.org/v2/resources/${resource.id}/download`,
        fileName: safeFileName(resource.name),
        source: 'Spigot' as const,
    }));
};

const PluginsContainer = () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [provider, setProvider] = useState<Provider>('modrinth');
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<PluginResult[]>([]);
    const [loading, setLoading] = useState(false);
    const [installing, setInstalling] = useState<string | null>(null);
    const [error, setError] = useState('');

    const search = async (event?: FormEvent) => {
        event?.preventDefault();
        if (!query.trim() || provider === 'builtbybit') return;
        setLoading(true);
        setError('');
        setResults([]);
        try {
            setResults(provider === 'modrinth' ? await searchModrinth(query.trim()) : await searchSpigot(query.trim()));
        } catch (error) {
            setError('تعذر جلب البلوقنات من المصدر حالياً. حاول مرة أخرى.');
            clearAndAddHttpError({ error, key: 'plugins' });
        } finally {
            setLoading(false);
        }
    };

    const install = async (plugin: PluginResult) => {
        setInstalling(plugin.id);
        clearFlashes('plugins');
        try {
            await pullFile(uuid, plugin.url, plugin.fileName);
        } catch (error) {
            clearAndAddHttpError({ error, key: 'plugins' });
        } finally {
            setInstalling(null);
        }
    };

    useDeepCompareEffect(() => {
        setResults([]);
    }, [provider]);

    return (
        <ServerContentBlock title={'Plugins'}>
            <FlashMessageRender byKey={'plugins'} css={tw`mb-4`} />
            <div css={tw`bg-neutral-700 rounded p-4 mb-4`}>
                <h2 css={tw`text-lg text-neutral-100 mb-2`}>Plugin installer</h2>
                <p css={tw`text-sm text-neutral-300 mb-4`}>
                    Search trusted marketplace listings and install the selected JAR directly into this server&apos;s{' '}
                    <code>plugins</code> directory.
                </p>
                <div css={tw`flex flex-wrap gap-2 mb-4`}>
                    {(['modrinth', 'spigot', 'builtbybit'] as Provider[]).map((item) => (
                        <button
                            key={item}
                            type={'button'}
                            css={
                                item === provider
                                    ? tw`px-3 py-2 rounded bg-primary-500 text-white`
                                    : tw`px-3 py-2 rounded bg-neutral-600 text-neutral-200`
                            }
                            onClick={() => setProvider(item)}
                        >
                            {item === 'modrinth' ? 'Modrinth' : item === 'spigot' ? 'Spigot' : 'BuiltByBit'}
                        </button>
                    ))}
                </div>
                {provider === 'builtbybit' ? (
                    <div css={tw`text-sm text-neutral-200`}>
                        BuiltByBit requires an authenticated marketplace entitlement for automatic downloads. Open the
                        marketplace, download a licensed JAR, then upload it from the Files page into{' '}
                        <code>/plugins</code>.
                        <a
                            css={tw`block text-primary-300 mt-3`}
                            href={'https://builtbybit.com/resources/'}
                            target={'_blank'}
                            rel={'noreferrer'}
                        >
                            Browse BuiltByBit resources
                        </a>
                    </div>
                ) : (
                    <form css={tw`flex flex-col sm:flex-row gap-2`} onSubmit={search}>
                        <Input
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder={'Search plugins...'}
                            aria-label={'Plugin search'}
                        />
                        <Button type={'submit'} disabled={loading || !query.trim()} isLoading={loading}>
                            Search
                        </Button>
                    </form>
                )}
            </div>
            {error && <p css={tw`text-red-300 text-sm mb-4`}>{error}</p>}
            {loading && <Spinner size={'large'} centered />}
            <Can action={'file.create'}>
                <div css={tw`space-y-2`}>
                    {results.map((plugin) => (
                        <div
                            key={`${plugin.source}-${plugin.id}`}
                            css={tw`bg-neutral-700 rounded p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3`}
                        >
                            <div css={tw`min-w-0`}>
                                <div css={tw`flex items-center gap-2`}>
                                    <h3 css={tw`text-neutral-100 font-semibold truncate`}>{plugin.name}</h3>
                                    <span css={tw`text-xs text-neutral-300`}>{plugin.source}</span>
                                </div>
                                <p css={tw`text-sm text-neutral-300 mt-1`}>{plugin.description}</p>
                                <p css={tw`text-xs text-neutral-400 mt-2`}>
                                    {plugin.version ? `Version ${plugin.version}` : 'Latest version'}
                                    {plugin.author ? ` · ${plugin.author}` : ''}
                                    {plugin.downloads ? ` · ${plugin.downloads.toLocaleString()} downloads` : ''}
                                </p>
                            </div>
                            <Button
                                size={'small'}
                                disabled={installing !== null}
                                isLoading={installing === plugin.id}
                                onClick={() => install(plugin)}
                            >
                                Install to plugins
                            </Button>
                        </div>
                    ))}
                </div>
            </Can>
        </ServerContentBlock>
    );
};

export default PluginsContainer;
