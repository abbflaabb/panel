import React, { forwardRef } from 'react';
import { Form } from 'formik';
import styled, { keyframes } from 'styled-components/macro';
import { breakpoint } from '@/theme';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const pulse = keyframes`
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
`;

const scanline = keyframes`
    0% { transform: translateY(-100%); }
    100% { transform: translateY(100vh); }
`;

const fadeIn = keyframes`
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
`;

const PageWrapper = styled.div`
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #060b06;
    position: relative;
    overflow: hidden;

    &::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(rgba(29, 185, 84, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(29, 185, 84, 0.03) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    &::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(61, 220, 82, 0.15), transparent);
        animation: ${scanline} 8s linear infinite;
        pointer-events: none;
    }
`;

const GlowOrb = styled.div`
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;

    &.orb1 {
        width: 400px;
        height: 400px;
        background: rgba(22, 163, 74, 0.08);
        top: -100px;
        left: -100px;
    }

    &.orb2 {
        width: 300px;
        height: 300px;
        background: rgba(61, 220, 82, 0.05);
        bottom: -50px;
        right: -50px;
    }
`;

const Container = styled.div`
    width: 100%;
    max-width: 420px;
    padding: 16px;
    animation: ${fadeIn} 0.5s ease;
    position: relative;
    z-index: 1;

    ${breakpoint('sm')`
        max-width: 440px;
    `};
`;

const Card = styled.div`
    background: rgba(13, 20, 13, 0.95);
    border: 1px solid rgba(61, 220, 82, 0.15);
    border-radius: 16px;
    padding: 40px 36px;
    box-shadow: 0 0 0 1px rgba(61, 220, 82, 0.05), 0 20px 60px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(61, 220, 82, 0.08);
    backdrop-filter: blur(20px);
    position: relative;
    overflow: hidden;

    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(61, 220, 82, 0.4), transparent);
    }
`;

const LogoArea = styled.div`
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 32px;
`;

const LogoIcon = styled.div`
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #1a5c2a, #2d8a42);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 700;
    color: #7dff9a;
    font-family: 'IBM Plex Mono', monospace;
    margin-bottom: 16px;
    box-shadow: 0 0 20px rgba(61, 220, 82, 0.2);
    letter-spacing: -1px;
`;

const LogoBrand = styled.div`
    font-size: 20px;
    font-weight: 600;
    color: #c8e6c9;
    letter-spacing: 1px;
    font-family: 'IBM Plex Sans', sans-serif;
`;

const LogoSub = styled.div`
    font-size: 10px;
    color: #3ddc52;
    letter-spacing: 3px;
    text-transform: uppercase;
    font-family: 'IBM Plex Mono', monospace;
    margin-top: 4px;
    opacity: 0.8;
`;

const StatusBadge = styled.div`
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(15, 32, 16, 0.8);
    border: 1px solid rgba(61, 220, 82, 0.2);
    border-radius: 20px;
    padding: 4px 12px;
    margin-top: 12px;
    font-size: 10px;
    color: #4a8a4a;
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: 1px;

    span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #3ddc52;
        animation: ${pulse} 2s infinite;
        display: inline-block;
    }
`;

const Title = styled.h2`
    font-size: 13px;
    font-weight: 500;
    color: #5a8c5a;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: 'IBM Plex Mono', monospace;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(61, 220, 82, 0.08);
`;

const Footer = styled.p`
    text-align: center;
    color: #2d4a2d;
    font-size: 11px;
    margin-top: 20px;
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: 0.5px;

    a {
        color: #3a6a3a;
        text-decoration: none;
        transition: color 0.15s;

        &:hover {
            color: #4a8a4a;
        }
    }
`;

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => (
    <PageWrapper>
        <GlowOrb className='orb1' />
        <GlowOrb className='orb2' />
        <Container>
            <FlashMessageRender css={tw`mb-4`} />
            <Card>
                <LogoArea>
                    <LogoIcon>BM</LogoIcon>
                    <LogoBrand>BITmaHost</LogoBrand>
                    <LogoSub>Control Panel</LogoSub>
                    <StatusBadge>
                        <span />
                        SYSTEMS ONLINE
                    </StatusBadge>
                </LogoArea>
                {title && <Title>{title}</Title>}
                <Form {...props} ref={ref}>
                    {props.children}
                </Form>
            </Card>
            <Footer>
                &copy; 2025 - {new Date().getFullYear()}&nbsp;
                <a rel={'noopener nofollow noreferrer'} href={'https://discord.gg/TDFqfAJvQj'} target={'_blank'}>
                    PRIXI Software
                </a>
            </Footer>
        </Container>
    </PageWrapper>
));
